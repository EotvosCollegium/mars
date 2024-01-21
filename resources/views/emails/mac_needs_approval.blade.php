@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        {{ $reporter }} új vezetékes eszközt regisztrált, mely jóváhagyásra vár.
    </p>
    @component('mail::button', ['url' => route('internet.admin')])
    Részletek
    @endcomponent
@endcomponent
