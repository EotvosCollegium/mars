<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('role_objects')->insert([
            'role_id' => Role::where('name', 'collegist')->first()->id,
            'name' => 'resident-extern'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('role_objects')->where([
            'role_id' => Role::where('name', 'collegist')->first()->id,
            'name' => 'resident-extern'
        ])->delete();
    }
};
