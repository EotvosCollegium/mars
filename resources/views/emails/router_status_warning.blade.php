@component('mail::message')
<h1>@lang('mail.dear') {{ $recipient->name }}!</h1>
<p>
A {{ $router->ip }} IP-címmel rendelkező router a {{ $router->room }} számú szobában nem elérhető.
</p>
@endcomponent
