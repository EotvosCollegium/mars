@component('mail::message')
<h1>@lang('mail.dear') {{ $recipient->name }}!</h1>
<p>
@lang('router.router_is_down_warning_resident', ['room' => $router->room]) {{ config('contacts.mail_replyto_address')}}.
</p>
@endcomponent
