<div class="row">
    <div class="col s12">
        <h6>Feltöltött nyelvvizsgák:</h6>
        <blockquote>
            <ul>
            @forelse ($user->educationalInformation?->languageExams?->sortBy('date') ?? [] as $exam)
                <li>
                    <a href="/{{ $exam->path }}">
                        {{ __('role.'.$exam->language) }} - {{ $exam->level }}</a>
                    @if($exam->wasBeforeEnrollment and !isset($application))
                    ({{ $exam->type}}, {{$exam->date->format('Y-m')}}, collegista státusz előtt szerezve)
                    @else
                    ({{ $exam->type}}, {{$exam->date->format('Y-m')}})
                    @endif
                </li>
            @empty
                <li>Nincs nyelvvizsga feltöltve.</li>
            @endforelse
        </blockquote>
    </div>
</div>
@if($user->educationalInformation)
<form method="POST" action="{{ route('users.language_exams.upload', ['user' => $user]) }}"
        enctype='multipart/form-data'>
    @csrf
    <div class="row">
        <x-input.file s=12 m=12 id="file" size="2000000" accept=".pdf,.jpg,.png,.jpeg" text="Új nyelvvizsga feltöltése" required/>
        <x-input.select s=12 m=3 id="language" text="Nyelv"
            :elements="App\View\Components\Input\Select::convertArray(array_merge(config('app.alfonso_languages'), ['other' => ' Egyéb']))"/>
        <x-input.select s=12 m=3 id="level" text="Szint" :elements="['A1', 'A2', 'B1', 'B2','C1', 'C2']"
            helper="Nem kötelező kitölteni, ha nem lehet átszámítani a fenti értékekre."/>
        <x-input.text s=12 m=3 id="type" text="Típus" helper="IELTS, Cambridge, ..." required/>
        <x-input.datepicker s=12 m=3 id="date" text="Dátum" required/>
    </div>
    <x-input.button only_input class="right" text="general.upload"/>
    <blockquote>A feltölteni kívánt fájlok maximális mérete: 2 MB, az engedélyezett formátumok: .pdf, .jpg,
        .jpeg, .png
    </blockquote>
</form>
@else
<p>A nyelvvizsgák feltöltése előtt töltse ki a tanulmányi adatait.</p>
@endif
