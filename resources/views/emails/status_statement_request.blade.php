@component('mail::message')
<h1>@lang('mail.dear') {{ $recipent??'asd' }}!</h1>
<p>
@lang('mail.status_statement_request')<br>
</p>
@component('mail::button', ['url' => route('secretariat.status-update.show')])
@lang('general.show')
@endcomponent
<p>@lang('mail.administrators')</p>
@endcomponent