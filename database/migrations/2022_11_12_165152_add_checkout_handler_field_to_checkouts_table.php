<?php

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
        Schema::table('checkouts', function (Blueprint $table) {
            $table->foreignId('handler_id')->nullable()->references('id')->on('users');
        });

        $student_council_role_id = DB::table('roles')->where('name', 'student-council')->first()->id;
        $economic_vice_president_object_id = DB::table('role_objects')->where('role_id', $student_council_role_id)->where('name', 'economic-vice-president')->first()->id;

        $economic_vice_president_user = DB::table('role_users')->where('role_id', $student_council_role_id)->where('object_id', $economic_vice_president_object_id);

        if($economic_vice_president_user->exists()) {
            DB::table('checkouts')->where('name', 'VALASZTMANY')->update([
                'handler_id' => $economic_vice_president_user->first()->id
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checkouts', function (Blueprint $table) {
            $table->removeColumn('handler_id');
        });
    }
};
