@component('mail::message')
    <h1>@lang('mail.dear') Collegisták!</h1>
    <p>
        Töltsd ki a szemeszter végi kérdőívet, mely alapján a Tanári Kar értékelni tudja a félévedet. A kérdőív
        kitöltése minden collegista számára kötelező.
    </p>
    @component('mail::button', ['url' => route('secretariat.evaluation.show')])
        Kitöltés
    @endcomponent
@endcomponent
