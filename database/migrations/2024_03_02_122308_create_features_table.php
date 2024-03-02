<?php

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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text("description");
            $table->boolean('enabled');
            $table->timestamps();
        });

        $disabled_names_and_descriptions = [
            "caching" => "Allow caching (event, route, view, config), recommended for production, not recommended for testing"
        ];

        foreach($disabled_names_and_descriptions as $name => $desc) {
            if (DB::table('features')->where('name', $name)->doesntExist()) {
                DB::table('features')->insert([
                    'name' => $name,
                    "description" => $desc,
                    'enabled' => false,
                ]);
            }
        }

        $enabled_names_and_descriptions = [
            "backup.google.drive" => "Automatically upload DB dumps to Google Drive",
            "printing" => "Enable printing for collegists",
            "internet" => "Enable internet module",
            "internet.wired" => "Enable wired internet module",
            "internet.wireless" => "Enable wireless internet module",
            "internet.wireless.connections" => "Keep track of wireless connections",
            "internet.wireless.routers" => "Keep track of wireless routers",
            "epistola" => "Allow epistola to work",
            "mr_and_miss" => "Allow Mr. and Miss to work",
            "economic_treasury" => "Allow economic committee to have a treasury",
            "administrator_treasury" => "Allow administrators to have a treasury",
            "community_service" => "Allow users to administer community service",
            "general_assembly" => "General assembly",
            "application" => "Enable application subsystem",
            "guests" => "Enable guests",
            "faults" => "Enable fault list",
            "rooms" => "Enable room management",
        ];

        foreach($enabled_names_and_descriptions as $name => $desc) {
            if (DB::table('features')->where('name', $name)->doesntExist()) {
                DB::table('features')->insert([
                    'name' => $name,
                    "description" => $desc,
                    'enabled' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('features');
    }
};
