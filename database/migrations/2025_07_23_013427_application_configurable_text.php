<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::table('configurable_texts')->insert([
            'key' => 'APPLICATION_FILE_RESUME',
            'rawtext' => 'Hagyományos, leíró jellegű önéletrajz (**nem** Europass típusú). Feltétlenül térjen ki a szakmai motivációira, tanulmányi- és versenyeredményeire.',
        ]);

        \DB::table('configurable_texts')->insert([
            'key' => 'APPLICATION_FILE_BESOROLASI_HATAROZAT',
            'rawtext' => 'A képzésre frissen felvételt nyert hallgatók esetén a Felvi *Hivatalos dokumentumok* menüpontjából letölthető besorolási határozatot kérjük feltölteni.

Kérjük, figyeljen arra, hogy ne más, a felvételt igazoló dokumentumot (pl. felvételi határozat) töltsön fel!',
        ]);

        \DB::table('configurable_texts')->insert([
            'key' => 'APPLICATION_FILE_ERETTSEGI',
            'rawtext' => 'Érettségi bizonyítványa, valamint érettségi tanúsítványai, ha rendelkezik ilyenekkel (az érettségi törzslapkivonatot **nem** szükséges feltöltenie).',
        ]);

        \DB::table('configurable_texts')->insert([
            'key' => 'APPLICATION_FILE_ELVEGZETT_FELEV',
            'rawtext' => 'Igazolás minden eddig elvégzett egyetemi félévéről (pl. leckekönyv, törzslap-kivonat, diplomamelléklet stb.).',
        ]);

        \DB::table('configurable_texts')->insert([
            'key' => 'APPLICATION_FILE_DIPLOMA',
            'rawtext' => 'Az összes korábban elvégzett egyetemi képzésének diplomája.',
        ]);

        \DB::table('configurable_texts')->insert([
            'key' => 'APPLICATION_FILE_APPLICATION_CUSTOM',
            'rawtext' => 'Ide tölthet fel minden olyan dokumentumot, igazolást, amelyet fontosnak tart a jelentkezésével kapcsolatban. Például: oklevelek, eredményekről szóló igazolások, tanári ajánlás.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('configurable_texts')->where('key', 'APPLICATION_FILE_RESUME')->delete();
        \DB::table('configurable_texts')->where('key', 'APPLICATION_FILE_BESOROLASI_HATAROZAT')->delete();
        \DB::table('configurable_texts')->where('key', 'APPLICATION_FILE_ERETTSEGI')->delete();
        \DB::table('configurable_texts')->where('key', 'APPLICATION_FILE_ELVEGZETT_FELEV')->delete();
        \DB::table('configurable_texts')->where('key', 'APPLICATION_FILE_DIPLOMA')->delete();
        \DB::table('configurable_texts')->where('key', 'APPLICATION_FILE_APPLICATION_CUSTOM')->delete();
    }
};
