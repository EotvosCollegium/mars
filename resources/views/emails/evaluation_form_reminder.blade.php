@component('mail::message')
    <h1>@lang('mail.dear') Collegisták!</h1>
    <p>
        A szemeszter végi kérdőív kitöltésének határideje {{ $deadline }}.
    </p>
    <p>
        Még {{ $count }} collegista nem töltötte ki a kérdőívet.
        A kérdőív segítségével tudja a Tanári Kar értékelni a félévet, így kitöltése kötelező.
    </p>
    <p>
        A kérdőív senior hallgatók számára nem elérhető, a seniori beszámoló ettől függetlenül kerül lebonyolításra.
    </p>
    @component('mail::button', ['url' => route('secretariat.evaluation.show')])
        Kitöltés
    @endcomponent
@endcomponent
