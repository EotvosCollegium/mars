<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('role_users', function (Blueprint $table) {
            $table->dateTime('valid_from')->useCurrent();  // CURRENT_TIMESTAMP as default value
            $table->dateTime('valid_until')->nullable();
        });

        // There shall be a single 'resident' role with an optional expiry date;
        // used for both collegists and tenants.
        DB::table('roles')->updateOrInsert(
            ['name' => 'resident'],
            ['has_workshops' => 0, 'has_objects' => 0]
        );

        // Current resident collegists should get this role.
        $residentRoleId = DB::table('roles')->where('name', 'resident')->first()->id;
        $tenantRoleId = DB::table('roles')->where('name', 'tenant')->first()?->id;
        $collegistRoleId = DB::table('roles')->where('name', 'collegist')->first()->id;
        $residentObjectId = DB::table('role_objects')->where('name', 'resident')->first()->id;
        foreach(DB::table('role_users')->where('object_id', $residentObjectId)->pluck('user_id') as $userId) {
            DB::table('role_users')->insert(['role_id' => $residentRoleId, 'user_id' => $userId]);
        }

        foreach(DB::table('role_users')->where('role_id', $tenantRoleId)->pluck('user_id') as $userId) {
            DB::table('role_users')->updateOrInsert(
                ['user_id' => $userId, 'role_id' => $residentRoleId],
                ['valid_until' => User::find($userId)->personalInformation->tenant_until]
            );
        }

        DB::table('role_users')->where('role_id', $collegistRoleId)->update(['object_id' => null]);
        DB::table('roles')->where('name', 'collegist')->update(['has_objects' => 0]);

        DB::table('role_objects')->where('name', 'resident')->delete();
        DB::table('role_objects')->where('name', 'extern')->delete();
        DB::table('roles')->where('name', 'tenant')->delete();

        Schema::table('personal_information', function (Blueprint $table) {
            $table->dropColumn('tenant_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_information', function (Blueprint $table) {
            $table->date('tenant_until')->nullable();
        });

        // delete invalid roles
        DB::table('role_users')
            ->where('valid_from', '>', Carbon::now())
            ->orWhere('valid_until', '<=', Carbon::now())
            ->delete();

        DB::table('roles')->where('name', 'collegist')->update(['has_objects' => 1]);

        DB::table('roles')->updateOrInsert(
            ['name' => 'tenant'],
            ['has_workshops' => 0, 'has_objects' => 0]
        );
        DB::table('role_objects')->insert([
            'role_id' => Role::where('name', 'collegist')->first()->id,
            'name' => 'resident'
        ]);
        DB::table('role_objects')->insert([
            'role_id' => Role::where('name', 'collegist')->first()->id,
            'name' => 'extern'
        ]);

        $residentRoleId = DB::table('roles')->where('name', 'resident')->first()->id;
        $tenantRoleId = DB::table('roles')->where('name', 'tenant')->first()->id;
        $collegistRoleId = DB::table('roles')->where('name', 'collegist')->first()->id;
        $residentObjectId = DB::table('role_objects')->where('name', 'resident')->first()->id;
        $externObjectId = DB::table('role_objects')->where('name', 'extern')->first()->id;
        foreach(DB::table('role_users')->where('role_id', $collegistRoleId)->pluck('user_id') as $userId) {
            if (DB::table('role_users')
                    ->where('role_id', $residentRoleId)->where('user_id', $userId)
                    ->whereNull('valid_until')  // not a resident-extern
                    ->exists()) {
                DB::table('role_users')
                    ->where('role_id', $collegistRoleId)
                    ->where('user_id', $userId)
                    ->update(['object_id' => $residentObjectId]);
            } else {
                DB::table('role_users')
                    ->where('role_id', $collegistRoleId)
                    ->where('user_id', $userId)
                    ->update(['object_id' => $externObjectId]);
            }
        }
        foreach(DB::table('role_users')->where('role_id', $residentRoleId)->get() as $row) {
            $userId = $row->user_id;
            if (DB::table('role_users')
                    ->where('role_id', $collegistRoleId)->where('user_id', $userId)
                    ->doesntExist()) {
                DB::table('role_users')
                    ->insert([
                        'role_id' => $tenantRoleId,
                        'user_id' => $userId
                    ]);
                DB::table('personal_information')->where('user_id', $userId)
                    ->update(['tenant_until' => $row->valid_until]);
            }
        }

        DB::table('roles')->where('name', 'resident')->delete();

        Schema::table('role_users', function (Blueprint $table) {
            $table->dropColumn('valid_from');
            $table->dropColumn('valid_until');
        });
    }
};
