@component('mail::message')
    <h1>@lang('mail.dear') {{$recipient}}!</h1>
    <p>
        @lang('reservations.reservation-verified')
        @lang('reservations.verifier'): {{$approver}}.
    </p>
    <ul>
        @if (isset($reservation->title))
        <li>@lang('reservations.title'): {{$reservation->title}}</li>
        @endif
        <li>@lang('reservations.room'): {{$reservation->reservableItem->name}}</li>
        <li>@lang('reservations.from'): {{$reservation->from}}</li>
        <li>@lang('reservations.until'): {{$reservation->until}}</li>
        <li>@lang('reservations.note'): {{$reservation->note}}</li>
    </ul>
@endcomponent
