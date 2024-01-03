@component('mail::message')
    <h1>@lang('mail.dear') Rendszergazdák!</h1>
    <p>
        {{ $reporter }} az imént jelezte, hogy kifogyott a papír a nyomtatóból.
    </p>
@endcomponent
