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
                @if($user->application->submitted)
                    <span class="green-text">Beadva.</span>
                @else
                    <span class="coli-text text-orange"><i>Folyamatban...</i></span>
                @endif
            </h6>

            <h6>Jelentkezési határidő:
                <i>{{ $deadline?->format('Y-m-d H:i') }}</i>
                @if ($deadline_extended)
                    <small class="coli-text text-orange">(Meghosszabbítva)</small>
                @endif
            </h6>
            @if(!$user->application->submitted)
                Hátra van: <i>{{ (int)\Carbon\Carbon::now()->diffInDays($deadline, false) }}</i> nap.
            @endif

            <blockquote>
                @if(!$user->application->submitted)
                    <p>A jelentkezése jelen állapotában még nem látható a felvételiztető bizottság számára! </p>

                    <ul class="browser-default">
                        <li>Jelentkezése bármikor félbeszakítható: a regisztrációnál megadott e-mail címmel és jelszóval
                            belépve bármikor visszatérhet erre az oldalra, és folytathatja az űrlap kitöltését.</li>
                        <li>Minden mező kötelező, ahol az ellenkezője nincs külön jelezve.</li>
                        <li>Miután minden szükséges kérdést megválaszolt és fájlt feltöltött,
                            véglegesítse jelentkezését a lap alján lévő gombra kattintva.
                            Kérjük, figyeljen a határidőre, mert utána már nem lesz lehetősége véglegesítésre.</li>
                    </ul>

                    <p>Amennyiben bármi kérdése lenne a felvételivel kapcsolatban, kérjük, írjon a
                        <a href="mailto:{{config('mail.secretary_mail')}}">{{config('mail.secretary_mail')}}</a> e-mail címre.
                        Ha technikai probléma adódna,
                        jelezze felénk a <a href="mailto:{{config('mail.sys_admin_mail')}}">{{config('mail.sys_admin_mail')}}</a>
                        e-mail-címen.
                    </p>
                @else
                    <p>Köszönjük, hogy jelentkezett az Eötvös Collegiumba!</p>
                    <p>A felvételire behívottak névsora és a további teendők a
                        <a href="https://eotvos.elte.hu/felveteli">Collegium honlapján</a> lesznek majd elérhetőek!</p>
                @endif
            </blockquote>
            @foreach ($errors->all() as $error)
                <blockquote class="error">{{ $error }}</blockquote>
            @endforeach
        </div>
    </div>
    @if(!$user->application->submitted)
        <nav class="nav-extended">
            <div class="nav-content">
                <ul class="tabs tabs-transparent">
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'personal']) }}"
                           class="{{request()->get('page') == 'personal' ? "active" : ""}}">Általános</a></li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'educational']) }}"
                           class="{{request()->get('page') == 'educational' ? "active" : ""}}">Tanulmányok</a></li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'questions']) }}"
                           class="{{request()->get('page') == 'questions' ? "active" : ""}}">Egyéb kérdések</a></li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'files']) }}"
                           class="{{request()->get('page') == 'files' ? "active" : ""}}">Fájlok</a></li>
                </ul>
            </div>
        </nav>
        @yield('form')
    @else
        @include('auth.application.application', ['user' => $user])
    @endif
    @if($user->application->submitted)
        @include('network.internet.wifi_password', ['internet_access' => $user->internetAccess, 'application' => true])
    @endif

    @if(request()->get('page') != 'submit')
    <x-input.button href="{{ route('application', ['page' => 'submit']) }}" style="margin-bottom: 40px"
       class="right primary" text="Ellenőrzés és véglegesítés" />
    @endif
@endsection
