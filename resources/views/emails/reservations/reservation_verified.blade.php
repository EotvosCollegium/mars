@component('mail::message')
    <h1>@lang('mail.dear') {{ $reservation->user->name }}!</h1>
    <p>
        {{ $approver }} @lang('reservations.has_approved_your_reservation')
    </p>
    <ul>
        <li>@lang('reservations.room'): {{ $reservation->reservableItem->name }}</li>
        <li>@lang('reservations.from'): {{ $reservation->reserved_from }}</li>
        <li>@lang('reservations.until'): {{ $reservation->reserved_until }}</li>
    </ul>
@endcomponent
