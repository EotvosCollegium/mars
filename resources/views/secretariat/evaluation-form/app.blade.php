@extends('layouts.app')
@section('title')
<i class="material-icons left">edit_note</i>Szemeszter végi értékelés
@endsection

@section('content')
<div class="row">
    <div class="col">
        <blockquote>
            <p>Töltsd ki a szemeszter végi értékelő kérdőívet.</p>
            <p>Amennyiben bármilyen ok miatt nem lehet kitölteni a valós adatokat,
            bármilyen helyesbítést, megjegyzést írj be a megfelelő mezőbe.</p>
            <p>Technikai probléma, észrevétel esetén mindenképp <a href="{{route('index_reportbug')}}">jelentsd a problémát</a>.
                Ha technikai gond miatt nem tudod megfelelően beadni a kérdőívet, akkor <a href="mailto:{{ config('mail.sys_admin_mail')}}">vedd fel a kapcsolatot a rendszergazdákkal</a>.</p>
            <p>Először ellenőrizd a személyes és tanulmányi adataid, minden hiányosságot és változást ments el.</p>
            <p>A kérvényeket a <a href="mailto:{{ config('mail.secretary_email')}}"> titkárság</a> számára küldd el.</p>
            <p>A válaszaidhoz a Tanári Kar, a Választmány elnöke és szakmai alelnöke, a titkárság, az igazgató és a rendszergazdák férnek hozzá.</p>
            <p>A kitöltés határideje: {{ $deadline }} </p>
        </blockquote>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="personal_information">
            <div class="card-content">
                <div class="card-title">Személyes adatok ellenőrzése</div>
                @include('user.personal-information', ['user' => user()])
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="educational_information" >
            <div class="card-content">
                <div class="card-title">Tanulmányi adatok ellenőrzése</div>
                @include('user.educational-information', ['user' => $user, 'faculties' => $faculties, 'workshops' => $workshops])
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="alfonso">
            <div class="card-content">
                <div class="card-title">Alfonsó</div>
                @include('user.alfonso-language-exams', ['user' => $user])
                <div class="row">
                    <div class="col">
                        <div class="divider" style="margin:10px"></div>
                    </div>
                </div>
                @include('user.alfonso', ['user' => $user])
                @include('user.alfonso-requirements', ['user' => $user])
                <form method="POST" action="">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="section" value="alfonso"/>
                        <x-input.text l=10 id="alfonso_note" :value="$evaluation?->alfonso_note" text="Megjegyzés, helyesbítés, egyéni elbírálás"/>
                        <x-input.button l=2 class="right" text="general.save" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="courses">
            <div class="card-content">
                <div class="card-title">EC-s kurzusok ({{\App\Models\Semester::current()->tag}})</div>
                @include('secretariat.evaluation-form.courses')
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="avg">
            <div class="card-content">
                <div class="card-title">Átlag</div>
                @include('secretariat.evaluation-form.avg')
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="general_assembly">
            <div class="card-content">
                <div class="card-title">Közgyűlés részvétel</div>
                @include('secretariat.evaluation-form.general_assembly_attendance')
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="community_service">
            <div class="card-content">
                <div class="card-title">Közösségi tevékenység</div>
                @include('secretariat.evaluation-form.community_service')
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="other">
            <div class="card-content">
                <div class="card-title">Egyéb tevékenység és eredmények</div>
                @include('secretariat.evaluation-form.other')
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card" id="feedback">
            <div class="card-content">
                <div class="card-title">Visszajelzés a Választmány felé</div>
                @include('secretariat.evaluation-form.feedback')
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12" id="status">
        @include('secretariat.evaluation-form.status_update')
    </div>
</div>
<div class="row">
    <div class="col">
        <blockquote>
            <p>Ha minden szükséges mezőt kitöltöttél és elmentettél, akkor nincs más teendőd. Köszönjük a közreműködést.</p>
        </blockquote>
    </div>
</div>

@push('scripts')
<script>
    //jump to the last section after form submit
    window.location.hash = "#{{ session('section') }}";
</script>
@endpush

@endsection
