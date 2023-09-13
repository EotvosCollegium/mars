<?php

use App\Models\GeneralAssemblies\GeneralAssembly;
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
        // The table is for the excused users of a general assembly
        Schema::create('general_assembly_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(GeneralAssembly::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->unique(['general_assembly_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_assembly_user');
    }
};
