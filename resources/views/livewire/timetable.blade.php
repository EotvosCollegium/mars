<div>
    {{-- navigation buttons --}}
    <div class="row">
        <div class="col s6 left-align">
            <x-input.button floating wire:click="step(-3)" icon="chevron_left" />
        </div>
        <div class="col s6 right-align">
            <x-input.button floating wire:click="step(3)" icon="chevron_right" />
        </div>
    </div>

    @php
    // if there is only one item:
    if (!isset($items)) {
        $items = [$item];
        $blocks = [$blocks];
    }

    $itemCount = count($items);

    // these are percentages
    $dayCount = $firstDay->diffInDays($lastDay)+1;
    $columnWidth = 100.0 / ($dayCount * $itemCount);
    @endphp

    <table>
        <thead>
            <th style="width:5%;"></th>
            @php $day = $firstDay->copy(); @endphp
            @for($i = 0; $i < $dayCount; ++$i)
            <th style="text-align: center; width: {{95.0 / $dayCount}}%;" colspan="{{$itemCount}}"
                @if($day->isToday())
                class="coli blue white-text"
                @endif
            >
                {{$day->format('m.d. (l)');}}
            </th>
            @php $day->addDay(); @endphp
            @endfor
        </thead>
        <tbody>
            <tr>
                <th>0:00</th>
                <td rowspan="24" colspan="{{$dayCount*$itemCount}}" style="padding:0;">
                    {{-- the panel itself --}}
                    <div style="position: relative; height: 2000px; margin: 0;">
                        @for($i = 0; $i < $itemCount; ++$i)
                            @php
                            $item = $items[$i];
                            @endphp
                            @foreach($blocks[$i] as $block)
                                @php
                                $isReservation = !is_null($block['reservation_id']);
                                $isDisabled = !$isReservation &&
                                                ($item->isOutOfOrder() || $block['until'] < \Carbon\Carbon::now());
                                // here, we assume that $from is a midnight date
                                $dayOfWeek = floor($firstDay->diffInDays($block['from']));
                                $startHourFloat = $block['from']->hour + ($block['from']->minute / 60.0);
                                $endHourFloat = $block['until']->isMidnight()
                                                ? 24.0
                                                : ($block['until']->hour + ($block['until']->minute / 60.0));
                                @endphp
                                @if($isReservation)
                                <a href="{{ route('reservations.show', App\Models\Reservation::find($block['reservation_id'])) }}">
                                @elseif(!$isDisabled)
                                {{-- default values as GET request parameters --}}
                                <a href="{{ route('reservations.create', ['item' => $item])
                                            . "?from={$block['from']}&until={$block['until']}"
                                }}">
                                @endif
                                    @php
                                    if ($isReservation) {
                                        $reservation = \App\Models\Reservation::find($block['reservation_id']);
                                        $isOurs = $reservation->user?->is(user());
                                    } else {
                                        $reservation = null;
                                        $isOurs = null;
                                    }
                                    @endphp
                                    <div style="position: absolute;
                                                left: {{($dayOfWeek * $itemCount + $i) * $columnWidth}}%;
                                                width: {{$columnWidth}}%;
                                                top: {{$startHourFloat * 100.0 / 24.0}}%;
                                                height: {{($endHourFloat - $startHourFloat) * 100.0 / 24.0}}%;"
                                        @class([
                                            'timetable-block',
                                            'valign-wrapper', 'center-align',
                                            'red' => $isReservation && !$isOurs,
                                            'yellow' => $isOurs,
                                            'green' => !$isReservation && !$isDisabled,
                                            'grey' => $isDisabled,
                                            'darken-4' => $isReservation && App\Models\Reservation::find($block['reservation_id'])->verified
                                                            || !$isDisabled,
                                            'lighten-4' => $isReservation && !App\Models\Reservation::find($block['reservation_id'])->verified
                                    ])>
                                        @if(!is_null($reservation))
                                        {{$reservation->displayName()}}
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        @endfor
                    </div>
                </td>
            </tr>
            @for($hour = 1; $hour < 24; ++$hour)
            <tr>
                <th>{{$hour}}:00</th>
            </tr>
            @endfor
        </tbody>
    </table>
</div>