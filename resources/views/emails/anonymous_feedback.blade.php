@component('mail::message')
<h1>@lang('mail.dear') {{ $recipent }}!</h1>
<p>
Egy névtelen felhasználó visszajelzést küldött a Választmány felé. A visszajelzést csak az elnök és a CHÖK titkára kapja meg.
</p>
<p>A visszajelzés szövege:<br>
{{ $feedback }}
</p>
<p>@lang('mail.administrators')</p>
@endcomponent
