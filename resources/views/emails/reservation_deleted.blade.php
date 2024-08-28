@component('mail::message')
    <h1>@lang('mail.dear') {{ $reservation->user->name }}!</h1>
    <p>
        {{ $deleter }}
        @if($reservation->verified)
            @lang('reservations.has_deleted_your_reservation')
        @else
            @lang('reservations.has_rejected_your_reservation')
        @endif
    </p>
    <ul>
        <li>@lang('reservations.room'): {{ $reservation->reservableItem->name }}</li>
        <li>@lang('reservations.from'): {{ $reservation->reserved_from }}</li>
        <li>@lang('reservations.until'): {{ $reservation->reserved_until }}</li>
    </ul>
@endcomponent
