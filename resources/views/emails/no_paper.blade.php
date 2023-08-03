@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        {{ $reporter }} az imént jelezte, hogy kifogyott a papír a nyomtatóból.
    </p>
@endcomponent
