@component('mail::message')
<h1>@lang('mail.dear') {{ $recipient }}!</h1>
<p>
{{ $user }} új tagsági igazolást igényelt.
</p>
@component('mail::button', ['url' => $url])
@lang('mail.show')
@endcomponent
<p>@lang('mail.administrators')</p>
@endcomponent