@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-content">
            <div class="card-title center-align">Jelentkezés az Eötvös József Collegiumba</div>
            <p class="center-align"><a href="https://eotvos.elte.hu/felveteli">Pályázati felhívás és egyéb
                    információk</a></p>
        </div>
    </div>
    <div class="card">
        <div class="card-content">
            <h6>Jelentkezés státusza:
                @if ($user->application->submitted)
                    <span class="green-text">Beadva.</span>
                @else
                    <span class="coli-text text-orange"><i>Folyamatban.</i></span>
                @endif
            </h6>

            <h6>Jelentkezési határidő:
                <i>{{ $deadline?->format('Y-m-d H:i') }}</i>
                @if ($deadline_extended)
                    <small class="coli-text text-orange">(Meghosszabbítva)</small>
                @endif
            </h6>
            @if (!$user->application->submitted)
                Hátra van: <i>{{ (int) \Carbon\Carbon::now()->diffInDays($deadline, false) }}</i> nap.
            @endif

            <blockquote>
                @if (!$user->application->submitted)
                    <p>A jelentkezése jelen állapotában még nem látható a felvételiztető bizottság számára! </p>

                    <ul class="browser-default">
                        <li>Jelentkezése bármikor félbeszakítható: a regisztrációnál megadott e-mail címmel és jelszóval
                            belépve bármikor visszatérhet erre az oldalra, és folytathatja az űrlap kitöltését.</li>
                        <li>Minden mező kötelező, ahol az ellenkezője nincs külön jelezve.</li>
                        <li>Miután minden szükséges kérdést megválaszolt és fájlt feltöltött,
                            véglegesítse jelentkezését a lap alján lévő gombra kattintva.
                            Kérjük, figyeljen a határidőre, mert utána már nem lesz lehetősége véglegesítésre.</li>

                        <li>A jelentkezés véglegesítéséhez Neptun-kódjának megadása is szükséges.
                            Egyes karokon ezek létrehozása elhúzódhat, így szíves türelmét kérjük.
                            Amennyiben ez 08.06. 23:59-ig sem történik meg, kérjük, jelezze a
                            <a href="mailto:{{ config('mail.sys_admin_mail') }}">rendszergazdáknak</a>,
                            akik lehetőséget fognak biztosítani a Neptun-kód nélküli jelentkezésre.</li>

                        <li><strong>Amennyiben az Informatikai Műhelybe (is) jelentkezik,</strong> a személyes felvételit
                            megelőzően egy írásbeli, szakmai feladatsor megoldását kérjük online módon 2024. augusztus 10.
                            23:59:59-ig. Ennek elvégzése nagyjából 3-4 órát vesz igénybe;
                            a részletekről e-mailben fogjuk tájékoztatni.
                            Ha a jelentkezését már nagyrészt kitöltötte, akkor kérjük, hogy <a
                                href="https://forms.office.com/e/SseYJFgHg2">a linkelt kérdőívben</a> jelezze, hogy kéri a
                                feladatsort.</li>

                        <li><strong style="color:red;">Amennyiben lakhatása még nem biztosított,
                            javasoljuk a szociális kollégiumokba történő jelentkezést is.</strong><br />
                            A szociális kollégiumi felvételi az ittenitől teljesen függetlenül zajlik;
                            részletek <a href="https://www.elte.hu/kollegiumi-jelentkezes">az ELTE honlapján</a> olvashatók.</li>
                    </ul>

                    <p>Amennyiben bármi kérdése lenne a felvételivel kapcsolatban, kérjük, írjon a
                        <a href="mailto:{{ config('mail.secretary_mail') }}">{{ config('mail.secretary_mail') }}</a> e-mail
                        címre.
                        Ha technikai probléma adódna,
                        jelezze felénk a <a
                            href="mailto:{{ config('mail.sys_admin_mail') }}">{{ config('mail.sys_admin_mail') }}</a>
                        e-mail-címen.
                    </p>
                @else
                    <p>Köszönjük, hogy jelentkezett az Eötvös Collegiumba!</p>

                    @if ($user->application->appliedWorkshops()->where('name', \App\Models\Workshop::INFORMATIKA)->exists())
                    <p style="margin: 10px 0;">Mivel <strong>az Informatikai Műhelybe (is) jelentkezett,</strong> a személyes felvételit
                        megelőzően egy <strong style="color: red;">írásbeli, szakmai feladatsor</strong>
                        megoldását kérjük online módon
                        <strong>2024. augusztus 10. 23:59:59-ig</strong>.
                        Ennek elvégzése nagyjából 3-4 órát vesz igénybe;
                        a részletekről e-mailben fogjuk tájékoztatni.
                        Ha még nem tette meg, kérjük,
                        <a href="https://forms.office.com/e/SseYJFgHg2">a linkelt kérdőívben</a>
                        jelezze, hogy kéri a feladatsort.</p>
                    @endif

                    <p style="margin: 10px 0;"><strong style="color:red;">Amennyiben lakhatása még nem biztosított,
                        javasoljuk a szociális kollégiumokba történő jelentkezést is.</strong><br />
                        A szociális kollégiumi felvételi az ittenitől teljesen függetlenül zajlik;
                        részletek <a href="https://www.elte.hu/kollegiumi-jelentkezes">az ELTE honlapján</a> olvashatók.</p>

                    <p>A felvételire behívottak névsora és a további teendők a
                        <a href="https://eotvos.elte.hu/felveteli">Collegium honlapján</a> lesznek majd elérhetőek.
                    </p>
                @endif
            </blockquote>
            @foreach ($errors->all() as $error)
                <blockquote class="error">{{ $error }}</blockquote>
            @endforeach
        </div>
    </div>
    @if (!$user->application->submitted)
        <nav class="nav-extended">
            <div class="nav-content">
                <ul class="tabs tabs-transparent">
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'personal']) }}"
                            class="{{ request()->get('page') == 'personal' ? 'active' : '' }}">Általános</a>
                    </li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'educational']) }}"
                            class="{{ request()->get('page') == 'educational' ? 'active' : '' }}">Tanulmányok</a>
                    </li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'questions']) }}"
                            class="{{ request()->get('page') == 'questions' ? 'active' : '' }}">Egyéb kérdések</a>
                    </li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'files']) }}"
                            class="{{ request()->get('page') == 'files' ? 'active' : '' }}">Fájlok</a>
                    </li>
                </ul>
            </div>
        </nav>
        @yield('form')
    @else
        @include('auth.application.application', ['user' => $user])
    @endif
    @if ($user->application->submitted)
        @include('network.internet.wifi_password', [
            'internet_access' => $user->internetAccess,
            'application' => true,
        ])
    @endif

    @if (request()->get('page') != 'submit')
        <x-input.button href="{{ route('application', ['page' => 'submit']) }}" style="margin-bottom: 40px"
            class="right coli blue" text="Ellenőrzés és véglegesítés" />
    @endif
@endsection
