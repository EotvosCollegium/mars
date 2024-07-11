<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('long_answers', function (Blueprint $table) {
            $table->text('text')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('long_answers')->whereNull('text')->update(['text' => '-']); // a dash
        Schema::table('long_answers', function (Blueprint $table) {
            $table->text('text')->nullable(false)->change();
        });
    }
};
