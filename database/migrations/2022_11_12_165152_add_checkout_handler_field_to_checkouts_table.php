<?php

use App\Models\Checkout;
use App\Models\Role;
use App\Models\User;
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

        $role = Role::studentsCouncil();
        $object = $role->getObject(Role::ECONOMIC_VICE_PRESIDENT);
        Checkout::studentsCouncil()->update([
            // here, we do not have 'valid_from' and 'valid_until' fields yet
            'handler_id' => User::withRole(Role::studentsCouncil(), Role::ECONOMIC_VICE_PRESIDENT, includesExpired: true)
                                ->first()?->id

        ]);
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
