@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        @if($outOfOrder)
        @lang('reservations.became_faulty')
        @else
        @lang('reservations.got_repaired')
        @endif
        {{ ' ' . $itemName . '.' }}
    </p>
    <p>
        @lang('reservations.check_reservations')
    </p>
@endcomponent
