@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        A szemeszter végi értékelő form elérhetővé vált a Collegium BA/BSc, MA/MSc, osztatlan szakos hallgatói számára számára.
    </p>
    <p>
        A jelenlegi határideje: {{ $deadline }}. A határidő módosítható a rendszergazdák segítségével.
    </p>
    <p>
        A kérdőív kitöltése kötelező, ellenkező esetben a rendszer automatikusan alumnivá állítja a collegistákat.
    </p>
    <p>
        A kérdőív senior hallgatók számára nem elérhető, a seniori beszámoló ettől függetlenül kerül lebonyolításra.
        A seniori beszámolók alapján a seniorok státuszának beállítását manuálisan kell elvégezni.
    </p>
    <p>
        A kérdőív eredményei letölthetőek a <a href="{{ route('users.index') }}">@lang("general.users")</a> menüpont
        alatt. A táblázatban az "értékelés" fül alatt találhatóak az eddigi kitöltések. (A határidő lejáratáig még
        szabadon módosíthatnak az értékeken.)<br/>
        Az eredményekhez a műhelyvezetők is hozzáférnek.
    </p>
@endcomponent
