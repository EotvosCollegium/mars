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
        Schema::rename("custom", "configurable_texts");
        Schema::table('configurable_texts', function($table) {
            $table->unsignedTinyInteger('workshop_id')->nullable();

            $table->string('summarize')->storedAs("CONCAT(`key`, ';', COALESCE(`workshop_id`, ''))");

            $table->longText('text')->nullable()->change();
            $table->renameColumn('text', 'rawtext');

            $table->unique(['summarize']);

            $table->foreign('workshop_id')->references('id')->on('workshops');
        });

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICANT_REGISTRATION",
            "rawtext" => "Itt az Eötvös Collegium felvételijére lehet regisztrálni. Ha vendégként internetet szeretnél igénybe venni, akkor [a vendégregisztrációt kell elvégezned](/register/guest).

If you are a tenant, you [should fill out a different form](/register/guest).

Ha rendelkezel már Urán fiókkal, [belépést követően](/login) adhatod le jelentkezésed.

###### Az ELTE Eötvös József Collegium egy szakkollégium, nem szociális kollégium. Mielőtt regisztrálnál itt feltétlen nézz utána, hogy mi a különbség a szakkollégiumok és a szociális kollégiumok között.

Az Eötvös Collegiummal kapcsolatban információkat a [honlapunkon](https://eotvos.elte.hu/), valamint a [felvételi felhívásban](https://eotvos.elte.hu/felveteli) találhatsz. Ha további kérdésed van, bátran keresd a [Választmányt](mailto:STUDENT_COUNCIL_EMAIL), illetve a [Titkárságot](mailto:SECRETARIAT_EMAIL)! Technikai probléma esetén pedig szólj a [rendszergazdáknak](mailto:SYSADMIN_EMAIL).

A szociális kollégiumokkal kapcsolatban további információt a [kollégiumok oldalán](https://www.elte.hu/kollegiumok) érhetsz el. A szociális kollégiumokkal kapcsolatban segítséget nyújtani nem tudunk."
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "TENANT_REGISTRATION",
            "rawtext" => "###### Ez az oldal fizetővendégeknek szól, a felvételihez nem itt kell regisztrálni!

[Ha felvételizni szeretnél, kattints ide](/register).

Ha vendégként internetet szeretnél igénybe venni, akkor az alábbi űrlapot kell kitöltened.

If you are a tenant, you are in the right place."
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_QUESTION2",
            "rawtext" => "Miért kíván a Collegium tagja lenni?"
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_QUESTION2_HELPER",
            "rawtext" => "≈300-500 karakter"
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_QUESTION3",
            "rawtext" => "Tervez-e tovább tanulni a diplomája megszerzése után? Milyen tervei vannak az egyetem után?"
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_QUESTION3_HELPER",
            "rawtext" => ""
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_QUESTION4",
            "rawtext" => "Részt vett-e közéleti tevékenységben? Ha igen, röviden jellemezze!"
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_QUESTION4_HELPER",
            "rawtext" => "Pl. diákönkormányzati tevékenység, önkéntesség, szervezeti tagság. (nem kötelező)"
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_INFORMATION_PRIOR_TO_FINALIZATION",
            "rawtext" => "A jelentkezése jelen állapotában még nem látható a felvételiztető bizottság számára!

- Jelentkezése bármikor félbeszakítható: a regisztrációnál megadott e-mail címmel és jelszóval belépve bármikor visszatérhet erre az oldalra, és folytathatja az űrlap kitöltését.
- Minden mező kötelező, ahol az ellenkezője nincs külön jelezve.
- Miután minden szükséges kérdést megválaszolt és fájlt feltöltött, véglegesítse jelentkezését a lap alján lévő gombra kattintva. Kérjük, figyeljen a határidőre, mert utána már nem lesz lehetősége véglegesítésre.
- A jelentkezés véglegesítéséhez Neptun-kódjának megadása is szükséges. Egyes karokon ezek létrehozása elhúzódhat, így szíves türelmét kérjük. Amennyiben ez TODO-ig sem történik meg, kérjük, jelezze a [rendszergazdáknak](mailto:SYSADMIN_EMAIL), akik lehetőséget fognak biztosítani a Neptun-kód nélküli jelentkezésre.
- **Egyes nyelvi műhelyekbe (Angol-Amerikai, Aurélien Sauvageot francia, Germanisztika, Olasz, Spanyol műhelyek) való jelentkezés feltétele az érintett nyelv legalább középfokú ismerete**. A műhelyekben kutatómunka folyik, pusztán nyelvtanulás céljából ne jelentkezz a nyelvi műhelyekbe.

###### Amennyiben lakhatása még nem biztosított, javasoljuk a szociális kollégiumokba történő jelentkezést is.
A szociális kollégiumi felvételi az ittenitől teljesen függetlenül zajlik; részletek az [ELTE honlapján](https://www.elte.hu/kollegiumi-jelentkezes) olvashatók.

Amennyiben bármi kérdése lenne a felvételivel kapcsolatban, kérjük, írjon a [Választmánynak](mailto:STUDENT_COUNCIL_EMAIL), illetve a [Titkárságnak](mailto:SECRETARIAT_EMAIL) e-mailben. Ha technikai probléma adódna, jelezze a [rendszergazdáknak](mailto:SYSADMIN_EMAIL)."
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_INFORMATION_AFTER_FINALIZATION",
            "rawtext" => "Köszönjük, hogy jelentkezett az Eötvös Collegiumba!

###### Amennyiben lakhatása még nem biztosított, javasoljuk a szociális kollégiumokba történő jelentkezést is.

A szociális kollégiumi felvételi az ittenitől teljesen függetlenül zajlik. Részletek az [ELTE honlapján](https://www.elte.hu/kollegiumi-jelentkezes) olvashatók.

A felvételire behívottak névsora és a további teendők a [Collegium honlapján](https://eotvos.elte.hu/felveteli) lesznek majd elérhetőek."
        ]);

        \DB::table('configurable_texts')->insert([
            "key" => "APPLICATION_FILES",
            "rawtext" => "A pályázatnak az alábbiakat kell tartalmaznia:

- hagyományos, leíró jellegű önéletrajz
- elsőéves egyetemistaként:
- szakfelvételi engedély/felvételi határozat (Neptun: Tanulmányok - Hivatalos bejegyzések menüpont alatt letölthető)
- érettségi bizonyítvány másolata
- lezárt egyetemi félévek esetén:
- diploma másolata vagy leckekönyv/törzslap kivonat az eddigi eredményekről
- opcionális: oklevelek, igazolások, szaktanári ajánlás

###### Az önéletrajz esszé jellegű összefüggő szöveg legyen, ne amerikai stílusú (például EuroPass).

Az interneten számos [minta található](https://www.google.com/search?q=hagyományos+leíró+jellegű+önéletrajz) hagyományos önéletrajzokra, valamint a [8. osztályos magyar könyv is bemutatja az önéletrajzok két típusát](https://nat2012.nkp.hu/tankonyv/magyar_nyelv_8/lecke_02_009)."
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configurable_texts', function($table) {
            $table->dropForeign(['workshop_id']);
            $table->dropUnique(['summarize']);
            $table->dropColumn('summarize');
            $table->renameColumn('rawtext', 'text');
            $table->dropColumn('workshop_id');
            $table->string('text')->nullable(false)->change();
        });
        Schema::rename("configurable_texts", "custom");
    }
};
