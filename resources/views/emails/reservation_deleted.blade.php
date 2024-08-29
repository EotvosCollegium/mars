@component('mail::message')
    <h1>@lang('mail.dear') {{ $owner }}!</h1>
    <p>
        {{ $deleter }}
        @if($reservationArray['verified'])
            @lang('reservations.has_deleted_your_reservation')
        @else
            @lang('reservations.has_rejected_your_reservation')
        @endif
        @if($isForAll)
            @lang('reservations.all_affected')
        @elseif(!is_null($reservationArray['group_id']))
            @lang('reservations.only_this_affected')
        @endif
    </p>
    <ul>
        <li>@lang('reservations.room'): {{ $itemName }}</li>
        <li>@lang('reservations.from'): {{ $reservationArray['reserved_from'] }}</li>
        <li>@lang('reservations.until'): {{ $reservationArray['reserved_until'] }}</li>
    </ul>
@endcomponent
