<?php

use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_should_attend_general_assembly', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignIdFor(GeneralAssembly::class, 'general_assembly_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'user_id')->constrained()->cascadeOnDelete();

            $table->unique(['general_assembly_id', 'user_id'], 'general_assembly_user_should_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_should_attend_general_assembly');
    }
};
