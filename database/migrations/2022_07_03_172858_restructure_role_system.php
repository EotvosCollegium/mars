<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestructureRoleSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('has_objects')->default(false)->after('name');
            $table->boolean('has_workshops')->default(false)->after('name');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });

        DB::table('roles')->whereIn('name', ['locale-admin', 'student-council', 'collegist'])
            ->update(['has_objects' => true]);
        DB::table('roles')->whereIn('name', ['workshop-administrator', 'workshop-leader', 'application-committee'])
            ->update(['has_workshops' => true]);
        DB::table('roles')->where('name', 'permission-handler')->delete();
        DB::table('roles')->where('name', 'print-admin')->delete();

        DB::table('roles')->where('name', 'internet-admin')->update(['name' => 'sys-admin']);

        Schema::create('role_objects', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->integer('role_id')->unsigned();
            $table->string('name');

            $table->foreign('role_id')->references('id')->on('roles');
        });

        DB::statement("ALTER TABLE role_users CHANGE COLUMN object_id object_id TINYINT UNSIGNED");
        Schema::table('role_users', function (Blueprint $table) {
            $table->tinyInteger('workshop_id')->nullable()->unsigned();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });

        $workshop_role_ids = DB::table('roles')->where('has_workshops', true)->select('id')->pluck('id')->toArray();
        foreach ($workshop_role_ids as $id) {
            DB::STATEMENT("UPDATE role_users SET workshop_id = object_id WHERE role_id = ?; ", [$id]);
            DB::STATEMENT("UPDATE role_users SET object_id = null WHERE role_id = ?; ", [$id]);
        }

        foreach (array_keys(config('app.locales')) as $locale) {
            DB::table('role_objects')->insert([
                'role_id' => DB::table('roles')->where('name', 'locale-admin')->first()->id,
                'name' => $locale
            ]);
        }

        foreach (['president', 'science-vice-president', 'economic-vice-president', 'cultural-leader', 'community-leader', 'communication-leader', 'sport-leader', 'cultural-member', 'community-member', 'communication-member', 'sport-member', 'kkt-handler'] as $role) {
            DB::table('role_objects')->insert([
                'role_id' => DB::table('roles')->where('name', 'student-council')->first()->id,
                'name' => $role
            ]);
        }

        DB::table('role_objects')->insert([
            'role_id' => DB::table('roles')->where('name', 'collegist')->first()->id,
            'name' => 'resident'
        ]);
        DB::table('role_objects')->insert([
            'role_id' => DB::table('roles')->where('name', 'collegist')->first()->id,
            'name' => 'extern'
        ]);

        DB::table('role_users')->where('role_id', DB::table('roles')->where('name', 'collegist')->first()->id)
            ->where('object_id', 1) // resident
            ->update(['object_id' => DB::table('role_objects')->where('name', 'resident')->first()->id]);
        DB::table('role_users')->where('role_id', DB::table('roles')->where('name', 'collegist')->first()->id)
            ->where('object_id', 2) // extern
            ->update(['object_id' => DB::table('role_objects')->where('name', 'extern')->first()->id]);
        DB::table('role_users')->where('role_id', DB::table('roles')->where('name', 'collegist')->first()->id)
            ->whereNull('object_id')
            ->delete();

        Schema::table('role_users', function (Blueprint $table) {
            $table->foreign('object_id')->references('id')->on('role_objects');
            $table->foreign('workshop_id')->references('id')->on('workshops');
        });

        //Set student council objects manually
        DB::table('role_users')->where('role_id', DB::table('roles')->where('name', 'student-council')->first()->id)->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
