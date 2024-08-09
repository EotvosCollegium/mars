@component('mail::message')
<h1>@lang('mail.dear') {{ $recipient }}!</h1>
<p>
@lang('mail.approved_registration')<br>
</p>
@component('mail::button', ['url' => config('app.url')])
@lang('general.login')
@endcomponent
@endcomponent
