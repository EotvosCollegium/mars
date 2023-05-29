@component('mail::message')
<h1>@lang('mail.dear') Collegista!</h1>
<p>
Töltsd ki a szemeszter végi kérdőívet, mely alapján a Tanári Kar értékelni tudja a félévedet.
A kérdőív kitöltése kötelező, ellenkező esetben a határidő lejárta után a rendszer automatikusan alumnivá állít.
</p>
@component('mail::button', ['url' => route('secretariat.evaluation.show')])
Kitöltés
@endcomponent
<p>@lang('mail.administrators')</p>
@endcomponent
