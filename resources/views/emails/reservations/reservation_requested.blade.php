@component('mail::message')
    @php
    // formatting date strings
    $reservedFrom = \Carbon\CarbonImmutable::make($reservation->reserved_from);
    $reservedUntil = \Carbon\CarbonImmutable::make($reservation->reserved_until);
    $reservedFrom->settings(['toStringFormat' => 'Y-m-d H:i']);
    $reservedUntil->settings(['toStringFormat' => 'Y-m-d H:i']);
    @endphp

    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        {{ $reservation->user->name }} az imént kérvényezett egy
        {{ ($reservation->isRecurring()
                && $reservation->reserved_from == $reservation->group->group_from
                && $reservation->reserved_until == $reservation->group->group_until)
            ? "{$reservation->group->frequency} naponta ismétlődő" : "egyszeri" }}
        foglalást
        a(z) {{ $reservation->reservableItem->name }} nevű terembe
        "{{ $reservation->title }}" néven,
        {{ $reservedFrom }}-től {{ $reservedUntil }}-ig.
        Kérjük, hagyja jóvá vagy törölje a foglalást
        <a href="{{ route('reservations.show', $reservation) }}">itt</a>.
    </p>
@endcomponent
