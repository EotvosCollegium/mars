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
                @include('auth.application.status', ['status' => $user->application->status])
            </h6>

            <h6>Jelentkezési határidő:
                <i>{{ $deadline->format('Y-m-d H:i') }}</i>
                @if ($deadline_extended)
                    <small class="coli-text text-orange">(Meghosszabbítva)</small>
                @endif
            </h6>
            @if($user->application->status == App\Models\ApplicationForm::STATUS_IN_PROGRESS)
            Hátra van: <i>{{ \Carbon\Carbon::now()->diffInDays($deadline, false) }}</i> nap.
            @endif

            <blockquote>
                @if($user->application->status == App\Models\ApplicationForm::STATUS_IN_PROGRESS)
                    <p>A jelentkezése jelen állapotában még nem látható a felvételiztető bizottság számára! </p>
                    <p>A jelentkezése bármikor félbe szakítható, a regisztrációnál megadott e-mail címmel és jelszóval
                        belépve bármikor visszatérhet erre az oldalra, és folytathatja az űrlap kitöltését.</p>
                    <p>Miután minden szükséges kérdést megválaszolt és fájlt feltöltött, kérjük, véglegesítse a
                        jelentkezését.</p>
                    <p>Kérjük figyeljen a határidőre, mert utána már nem lesz lehetősége véglegesíteni azt.</p>
                    <p>Amennyiben bármi kérdése lenne a felvételivel kapcsolatban, kérjük, írjon a
                        <a href="mailto:{{config('mail.secretary_mail')}}">{{config('mail.secretary_mail')}}</a> e-mail címre. Ha
                        technikai
                        probléma adódna, kérjük, jelezze felénk a <a href="mailto:{{config('mail.sys_admin_mail')}}">{{config('mail.sys_admin_mail')}}</a>
                        e-mail címen.
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
    @if($user->application->status == App\Models\ApplicationForm::STATUS_IN_PROGRESS)
        <nav class="nav-extended">
            <div class="nav-content">
                <ul class="tabs tabs-transparent">
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'personal']) }}"
                           class="@yield('personal-active')">Általános</a></li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'educational']) }}"
                           class="@yield('educational-active')">Tanulmányok</a></li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'questions']) }}"
                           class="@yield('questions-active')">Egyéb kérdések</a></li>
                    <li class="tab">
                        <a href="{{ route('application', ['page' => 'files']) }}"
                           class="@yield('files-active')">Fájlok</a></li>
                    <li class="tab right">
                        <a href="{{ route('application', ['page' => 'submit']) }}"
                           class="@yield('finalize-active')">Ellenőrzés és véglegesítés</a></li>
                </ul>
            </div>
        </nav>
        @yield('form')
    @else
        @include('auth.application.application', ['user' => $user])
    @endif
    @if($user->application->status != App\Models\ApplicationForm::STATUS_IN_PROGRESS && isset($user->internetAccess))
        @include('network.internet.wifi_password', ['internet_access' => $user->internetAccess, 'application' => true])
    @endif

@endsection
