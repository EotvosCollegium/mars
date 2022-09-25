<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCustomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::rename('home_page_news', 'custom');

        Schema::table('custom', function (Blueprint $table) {
            $table->string('key')->after('id');
        });

        DB::table('custom')->where('id', 1)->update(['key' => 'HOME_PAGE_NEWS_COLLEGISTS']);
        DB::table('custom')->insert(['key' => 'HOME_PAGE_NEWS', 'text' => '']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom');
    }
}
