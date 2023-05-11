@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        {{ $reporter}} egy internetes hibát jelentett be {{$user_os}} operációs rendszerről.
    </p>
    <p>
        Leírás: {{ $report }}
    </p>
@endcomponent
