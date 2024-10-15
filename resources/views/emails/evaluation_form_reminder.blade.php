@component('mail::message')
    <h1>@lang('mail.dear') Collegisták!</h1>
    <p>
        A szemeszter végi kérdőív kitöltésének határideje {{ $deadline }}.
    </p>
    <p>
        A kérdőív segítségével tudja a Tanári Kar értékelni a félévet, így kitöltése kötelező.
    </p>
    @component('mail::button', ['url' => route('secretariat.evaluation.show')])
        Kitöltés
    @endcomponent
@endcomponent
