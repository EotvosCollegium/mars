@php
// if there is only one item:
if (!isset($items)) {
    $items = [$item];
    $blocks = [$blocks];
}
$itemCount = count($items);

// Parameters for width, height etc.
// These are percentages.
$columnWidth = 100.0 / ($itemCount * $from->diffInDays($until));
$rowHeight = 100.0 / 24.0; // for one hour
@endphp

<div style="position: relative; height: 2000px;">
    @for ($i=0; $i<$itemCount; ++$i)
        @php
        $item = $items[$i];
        @endphp
        @foreach($blocks[$i] as $block)
            @php
            // We assume here that $from is a midnight date.
            $dayNumber = floor($from->diffInDays($block["from"]));
            $left = ($dayNumber * $itemCount + $i) * $columnWidth;
            $startHourFloat = $block["from"]->hour + $block["from"]->minute / 60.0;
            $endHourFloat =
            $block["until"]->isMidnight()
            ? (24.0)
            : ($block["until"]->hour + $block["until"]->minute / 60.0);
            $top = $startHourFloat * $rowHeight;
            $height = ($endHourFloat - $startHourFloat) * $rowHeight;
            $reservation = is_null($block['reservation_id']) ? null : App\Models\Reservation::find($block["reservation_id"]);
            @endphp

            @if(!is_null($reservation))
            <a href="{{ route('reservations.show', $block['reservation_id']) }}">
            @endif
            <div style="position: absolute; left: {{$left}}%; top: {{$top}}%; width: {{$columnWidth}}%; height: {{$height}}%"
            @class([
                'timetable-block',
                'red' => !is_null($reservation),
                'green' => is_null($reservation),
                'darken-4' => is_null($reservation) || $reservation->verified,
                'lighten-4' => !is_null($reservation) && !$reservation->verified
            ])>
                @if(!is_null($reservation))
                    {{ $reservation->displayName() }}
                @endif
            </div>
            @if(!is_null($reservation))
            </a>
            @endif
        @endforeach
    @endfor
</div>
