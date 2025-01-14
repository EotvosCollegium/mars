@component('mail::message')
    <h1>@lang('mail.dear') Collegisták!</h1>
    <p>
    A szemeszter végi értékelő form elérhetővé vált a Collegium BA/BSc, MA/MSc, osztatlan szakos hallgatói számára számára.
    Töltsd ki a szemeszter végi kérdőívet, mely alapján a Tanári Kar értékelni tudja a félévedet.
    A kérdőív kitöltése minden nem senior collegista számára kötelező.
    </p>
    <p>
    A kérdőív senior hallgatók számára nem elérhető, a seniori beszámoló ettől függetlenül kerül lebonyolításra.
    </p>
    @component('mail::button', ['url' => route('secretariat.evaluation.show')])
        Kitöltés
    @endcomponent
@endcomponent
