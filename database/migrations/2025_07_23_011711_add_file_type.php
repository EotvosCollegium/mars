<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    private const FILE_TYPES = [
        'profile_picture',
        'receipt',
        'resume',
        'besorolasi_hatarozat',
        'erettsegi',
        'elvegzett_felev',
        'diploma',
        'application_custom'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->enum('type', self::FILE_TYPES)->nullable(true)->after('user_id');
            $table->string('name')->nullable()->change();
        });

        DB::statement("
            UPDATE files
            SET type = CASE
            WHEN name = 'profile_picture' THEN 'profile_picture'
            WHEN name = 'receipt' THEN 'receipt'
            ELSE 'application_custom'
            END
        ");

        DB::statement("
            UPDATE files
            SET name = NULL
            WHERE type != 'application_custom'
        ");

        Schema::table('files', function (Blueprint $table) {
            $table->enum('type', self::FILE_TYPES)->nullable(false)->change();
            $table->renameColumn('name', 'description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            UPDATE files
            SET description = type
            WHERE description IS NULL
        ");

        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->renameColumn('description', 'name');
            $table->string('name')->nullable(false)->change();
        });
    }
};
