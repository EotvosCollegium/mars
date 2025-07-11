
@if(isset($application))
<blockquote>
<p>A Collegiumban az ALFONSÓ nyelvi program keretében nyelvoktatás folyik.
    <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok" target="_blank"
        style="text-decoration:underline">
        A program szabályzata elérhető itt.</a>
</p>
<p>Az igények előrejelzése miatt kérjük, adja meg, milyen nyelven szeretné elkezdeni a programot.</p>
<p>Amennyiben Önnek nem kötelező a szabályzat alapján részt vennie a nyelvi programban, akkor nem szükséges megjelölnie az ALFONSÓ program keretében elérni kívánt szintet.</p>
</blockquote>

@endif

<form method="POST" action="{{ route('users.update.alfonso', ['user' => $user]) }}">
    @csrf
    <div class="row">
        <x-input.select l=5 id="alfonso_language" text="Az Alfonsó program keretében választott nyelv"
                    value='{{ $user->educationalInformation?->alfonso_language }}'
                    :elements="App\View\Components\Input\Select::convertArray(config('app.alfonso_languages'))"
                    allow-empty="{{ isset($application) ? 'A szabályzat alapján nem fogok részt venni az ALFONSÓ programban' : 'Nem tanulok ALFONSÓt' }}"
                    :helper="isset($application) ? '(később módosítható, nem része a felvételi eljárásnak)' : ''"
                    />
        <x-input.select l=5 id="alfonso_desired_level" text="Elérni kívánt szint"
            :value='$user->educationalInformation?->alfonso_desired_level'
            :elements="['B2','C1']"
            allow-empty="{{ isset($application) ? 'A szabályzat alapján nem fogok részt venni az ALFONSÓ programban' : 'Nem tanulok ALFONSÓt' }}"
        />

        <x-input.button l=2 class="right" text="general.save" />
    </div>
</form>