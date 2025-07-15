
@if(isset($application))
<blockquote>
<p>A Collegiumban az ALFONSÓ nyelvi program keretében nyelvoktatás folyik.
    <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok" target="_blank"
        style="text-decoration:underline">
        A program szabályzata elérhető itt.</a>
</p>
<p>Az igények előrejelzése miatt kérjük, adja meg, milyen nyelven tervezi elkezdeni a programot.
Felvételt követően lehetőség lesz módosítani a nyelven és a szinten.</p>
<p>Amennyiben Önnek nem kötelező a szabályzat alapján részt vennie a nyelvi programban, akkor nem szükséges megjelölnie az ALFONSÓ program keretében elérni kívánt szintet.</p>
</blockquote>

@endif

<form method="POST" action="{{ route('users.update.alfonso', ['user' => $user]) }}">
    @csrf
    @if (user()->cannot('edit', $user))
        @markdown(__('user.data_cannot_be_edited_application'),
            [
                'SYSADMIN_EMAIL' => config('mail.sys_admin_mail'),
                'SECRETARIAT_EMAIL' => config('mail.secretary_mail'),
                'STUDENT_COUNCIL_EMAIL' => config('contacts.mail_valasztmany'),
            ]
        )
    @elseif(user()->cannot('editStaticEducationalInformation', $user))
        @markdown(__('user.some_data_cannot_be_edited'),
            [
                'SYSADMIN_EMAIL' => config('mail.sys_admin_mail'),
                'SECRETARIAT_EMAIL' => config('mail.secretary_mail'),
                'STUDENT_COUNCIL_EMAIL' => config('contacts.mail_valasztmany'),
            ]
        )
    @endif
    <div class="row">
        <x-input.select l=5 id="alfonso_language" text="Az Alfonsó program keretében választott nyelv"
                    value='{{ $user->educationalInformation?->alfonso_language }}'
                    :elements="App\View\Components\Input\Select::convertArray(config('app.alfonso_languages'))"
                    allow-empty="{{ isset($application) ? 'A szabályzat alapján nem fogok részt venni az ALFONSÓ programban' : 'Nem tanulok ALFONSÓt' }}"
                    :disabled="user()->cannot('editStaticEducationalInformation', $user)"
                    :asterisk="!$user->educationalInformation->alfonsoExempted()"
                    />
        <x-input.select l=5 id="alfonso_desired_level" text="Elérni kívánt szint"
            :value='$user->educationalInformation?->alfonso_desired_level'
            :elements="['B2','C1']"
            allow-empty="{{ isset($application) ? 'A szabályzat alapján nem fogok részt venni az ALFONSÓ programban' : 'Nem tanulok ALFONSÓt' }}"
            :disabled="user()->cannot('editStaticEducationalInformation', $user)"
            :asterisk="!$user->educationalInformation->alfonsoExempted()"
        />
        @can('editStaticEducationalInformation', $user)
        <x-input.button l=2 class="right" text="general.save"/>
        @endcan
    </div>
</form>