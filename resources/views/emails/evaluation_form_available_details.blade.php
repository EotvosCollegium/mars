@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        A szemeszter végi értékelő form elérhetővé vált a collegisták számára.
    </p>
    <p>
        A jelenlegi határideje: {{ $deadline }}. A határidő módosítható a rendszergazdák segítségével.
    </p>
    <p>
        A kérdőív kitöltése kötelező, ellenkező esetben a rendszer automatikusan alumnivá állítja a collegistákat.<br/>
        A státusz nyilvántartása miatt a (rövidített) kérdőív a seniorok számára is kötelezően kitöltendő, a seniori
        beszámoló mellett.
    </p>
    <p>
        A kérdőív eredményei letölthetőek a <a href="{{ route('users.index') }}">@lang("general.users")</a> menüpont
        alatt. A táblázatban az "értékelés" fül alatt találhatóak az eddigi kitöltések. (A határidő lejáratáig még
        szabadon módosíthatnak az értékeken.)<br/>
        Az eredményekhez a műhelyvezetők is hozzáférnek.
    </p>
@endcomponent
