@component('mail::message')
<h1>@lang('mail.dear') Collegista!</h1>
<p>
Töltsd ki a szemeszter végi értékelő kérdőívet. Amennyiben nem töltöd ki {{$deadline}}-ig, elveszíted a státuszod!
</p>
@component('mail::button', ['url' => route('secretariat.evaluation.show')])
Kitöltés
@endcomponent
<p>@lang('mail.administrators')</p>
@endcomponent
