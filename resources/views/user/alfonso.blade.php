
@if(isset($application))
<blockquote>
A Collegiumban az ALFONSÓ nyelvi program keretében nyelvoktatás folyik.
    <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok"
        style="text-decoration:underline">
        A program szabályzata elérhető itt.</a>
</blockquote>
@endif

@if($user->educationalInformation)
<form method="POST" action="{{ route('users.update.alfonso', ['user' => $user]) }}">
    @csrf
    <div class="row">
        <x-input.select l=5 id="alfonso_language" text="Az Alfonsó program keretében választott nyelv"
                    value='{{ $user->educationalInformation?->alfonso_language }}'
                    :elements="App\View\Components\Input\Select::convertArray(config('app.alfonso_languages'))"
                    allow-empty
                    :helper="isset($application) ? '(később módosítható)' : ''"
                    />
        <x-input.select l=5 id="alfonso_desired_level" text="Elérni kívánt szint"
            :value='$user->educationalInformation?->alfonso_desired_level'
            :elements="['B2','C1']"
            allow-empty
        />

        <x-input.button l=2 class="right" text="general.save" />
    </div>
</form>
@else
<p>Az ALFONSÓ nyelv megadása előtt adja meg a tanulmányi adatait.</p>
@endif


