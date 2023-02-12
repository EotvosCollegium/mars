@component('mail::message')
<h1>@lang('mail.dear') Collegista!</h1>
<p>
Add meg a státuszodat a következő szemeszterre! Amennyiben nem töltöd ki {{$deadline}}-ig, elveszíted a státuszod!
</p>
@component('mail::button', ['url' => route('secretariat.status-update.show')])
Kitöltés
@endcomponent
<p>@lang('mail.administrators')</p>
@endcomponent
