<form method="POST" action="">
    @csrf
    <div class="row">
        <x-input.text s=6 type="number" min="1" max="5" step="0.01" id="avg_now" text="Átlag ({{\App\Models\Semester::current()->tag}})" helper="(ha ismert, tizedesponttal)"/>
        <x-input.text s=6 type="number" min="1" max="5" step="0.01" id="avg_last" text="Átlag ({{\App\Models\Semester::previous()->tag}})" helper="(tizedesponttal)" />
    </div>
    <blockquote>
        <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok">CTSZK 7. § (4) b.</a>
        A collegiumi tagság automatikusan megszűnik, ha a hallgatónak a tanulmányi átlaga két egymást követő félévben 4,25 alá süllyed<br>
        i. ahol a hagyományos átlagszámítás az érvényes, melybe minden szöveges értékelésű és nullkredites tárgy is beleszámít, illetve a BTK-s és TáTK-s kezelési körben meghirdetett kurzusok esetében az elhagyott tanegység értéke nulla,<br>
        ii. a hallgató mentesül a 7. § (4) b. rendelkezés alól, amennyiben a műhelyvezető támogatásával a hallgató kérelmezésére kezdeményezett vizsgálat alapján teljesítménye mindkét kérdéses félévben az adott szakon azonos számú aktív félévvel rendelkező hallgatók kreditindexe alapján felállított lista legjobb 10%-ához tartozik
    </blockquote>
    <div class="row">
        <x-input.button class="right" text="general.save" />
    </div>
</form>
