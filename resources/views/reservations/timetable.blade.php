@php
// if there is only one item:
if (!isset($items)) {
    $items = [$item];
    $blocks = [$blocks];
}

$itemCount = count($items);

// these are percentages
$columnWidth = 100.0 / (7.0 * $itemCount);
@endphp

<div style="position: relative; height: 2000px;">
    @for($i = 0; $i < $itemCount; ++$i)
        @php
        $item = $items[$i];
        @endphp
        @foreach($blocks[$i] as $block)
            @php
            $isReservation = !is_null($block['reservation_id']);
            // here, we assume that $from is a midnight date
            $dayOfWeek = floor($from->diffInDays($block['from']));
            $startHourFloat = $block['from']->hour + ($block['from']->minute / 60.0);
            $endHourFloat = $block['until']->isMidnight()
                            ? 24.0
                            : ($block['until']->hour + ($block['until']->minute / 60.0));
            @endphp
            @if($isReservation)
            <a href="{{ route('reservations.show', App\Models\Reservation::find($block['reservation_id'])) }}">
            @endif
                <div style="position: absolute;
                            left: {{($dayOfWeek * $itemCount + $i) * $columnWidth}}%;
                            width: {{$columnWidth}}%;
                            top: {{$startHourFloat * 100.0 / 24.0}}%;
                            height: {{($endHourFloat - $startHourFloat) * 100.0 / 24.0}}%;"
                    @class([
                        'timetable-block',
                        'red' => $isReservation,
                        'green' => !$isReservation,
                        'darken-4'
                ])>
                    @if(!is_null($block['reservation_id']))
                    {{App\Models\Reservation::find($block['reservation_id'])->displayName()}}
                    @endif
                </div>
            @if($isReservation)
            </a>
            @endif
        @endforeach
    @endfor
</div>
