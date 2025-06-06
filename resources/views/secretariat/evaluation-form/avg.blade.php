<form method="POST" action="">
    @csrf
    <blockquote>
        Ha még nem vagy lezárva minden tárgyból, ne írd be az átlagodat! (A beküldési határidőig bármikor beírhatod majd.)
    </blockquote>
    <div class="row">
        <input type="hidden" name="section" value="avg"/>
        <x-input.text s=6 type="number" min="1" max="5" step="0.01" id="current_avg" :value="$evaluation?->current_avg" text="Átlag ({{$periodicEvent->semester->tag}})" />
        <x-input.text s=6 type="number" min="1" max="5" step="0.01" id="last_avg" :value="$evaluation?->last_avg" text="Átlag ({{$periodicEvent->semester->pred()->tag}})" />
    </div>
    <blockquote>
        <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok">CTSZK 8. § (4) b.</a>
        A collegiumi tagság automatikusan megszűnik, ha a hallgatónak a tanulmányi átlaga két egymást követő félévben 4,25 alá süllyed<br>
        i. ahol a hagyományos átlagszámítás az érvényes, melybe minden szöveges értékelésű és nullkredites tárgy is beleszámít, illetve a BTK-s és TáTK-s kezelési körben meghirdetett kurzusok esetében az elhagyott tanegység értéke nulla,<br>
        ii. a hallgató mentesül a 7. § (4) b. rendelkezés alól, amennyiben a műhelyvezető támogatásával a hallgató kérelmezésére kezdeményezett vizsgálat alapján teljesítménye mindkét kérdéses félévben az adott szakon vagy szakirányon azonos számú aktív félévvel rendelkező hallgatók kreditindexe alapján felállított lista legjobb 10%-ához tartozik,<br>
        iii. a hallgatót az igazgató a Collegiumban végzett közösségi munkájáért a Tanári Kar ajánlása alapján felmentheti, amennyiben a Választmány ezt hivatalosan igazolja
    </blockquote>
    <div class="row">
        <x-input.button class="right" text="general.save" />
    </div>
</form>
