<?php

use App\Models\Role;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('role_objects')->updateOrInsert(['role_id' => Role::firstWhere('name', 'student-council')->id, 'name' => 'kkt-handler'], []);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('role_objects')->where('name', 'kkt-handler')->delete();
    }
};
