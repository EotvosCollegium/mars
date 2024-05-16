@component('mail::message')
    <h1>@lang('mail.dear') {{ $reservation->user->name }}!</h1>
    <p>
        {{ $approver }} jóváhagyta; örülhetsz!
    </p>
    <p>
        Adatok: {{ json_encode($reservation) }}
    </p>
@endcomponent
