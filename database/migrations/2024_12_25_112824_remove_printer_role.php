<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $printer_role_id = DB::table('roles')->where('name', 'printer')->first()->id;

        DB::table('role_users')->where('role_id', $printer_role_id)->delete();

        DB::table('roles')->where('name', 'printer')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $printer_role_id = DB::table('roles')->insertGetId([
            'name' => 'printer'
        ]);

        foreach(DB::table('users')->where('verified', 1)->get() as $user) {
            DB::table('role_users')->insert([
                'role_id' => $printer_role_id,
                'user_id' => $user->id
            ]);
        }
    }
};
