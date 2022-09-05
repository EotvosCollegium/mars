@component('mail::message')
<h1>@lang('mail.dear') Collegista!</h1>
<p>
@lang('secretariat.status_statement_request', ['deadline' => $deadline])<br>
</p>
@component('mail::button', ['url' => route('secretariat.status-update.show')])
Kitöltés
@endcomponent
<p>@lang('mail.administrators')</p>
@endcomponent
