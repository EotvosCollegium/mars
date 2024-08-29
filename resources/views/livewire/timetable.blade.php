@php
// if there is only one item:
if (!isset($items)) {
    $items = [$item];
}

$itemCount = count($items);
$rowCount = $lastHour - $firstHour;

// these are percentages
$dayCount = $firstDay->diffInDays($lastDay)+1;
$columnWidth = 100.0 / ($dayCount * $itemCount);
@endphp

<div>
    {{-- navigation buttons --}}
    @if($isPrintVersion)
    <div class="navbuttons">
        <div>{{$firstDay->isoFormat('MM.DD.')}} – {{$lastDay->isoFormat('MM.DD.')}}</div>
        <x-input.button wire:click="step(-7)" text="Egy héttel előbb" />
        <x-input.button wire:click="step(7)" text="Egy héttel később" />
    </div>
    @else
    <div class="row">
        <div class="col s4 left-align">
            <x-input.button floating wire:click="step({{ -1 * $dayCount }})" icon="chevron_left" />
        </div>
        <div class="col s4 center-align">
            @if(1 == $itemCount)
            <a href="{{ route('reservations.items.show_print_version', $items[0]) }}" type="submit" class="waves-effect btn-floating grey">
                <i class="material-icons">print</i>
            </a>
            @endif
        </div>
        <div class="col s4 right-align">
            <x-input.button floating wire:click="step({{ $dayCount }})" icon="chevron_right" />
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:5%;"></th>
                @php $day = $firstDay->copy(); @endphp

                @for($i = 0; $i < $dayCount; ++$i)
                <th style="text-align: center; width: {{95.0 / $dayCount}}%;" colspan="{{$itemCount}}"
                    @if($day->isToday())
                    class="coli blue white-text"
                    @endif
                >
                    @if($isPrintVersion)
                    {{$day->isoFormat('dddd')}}
                    @else
                    {{$day->isoFormat('MM.DD. (dddd)');}}
                    @endif
                </th>
                @php $day->addDay(); @endphp
                @endfor
            </tr>
            @if($displayItemNames)
            <tr>
                <th style="width:5%;"></th>

                @for($i = 0; $i < $dayCount; ++$i)
                @for($j = 0; $j < $itemCount; ++$j)
                <th style="text-align: center; width: {{95.0 / ($dayCount * $itemCount)}}%;">
                    @if($items[$j]->out_of_order)
                    <s>
                    @endif
                        <a href="{{ route('reservations.items.show', $items[$j]) }}">{{ $items[$j]->name }}</a>
                    @if($items[$j]->out_of_order)
                    </s>
                    @endif
                </th>
                @endfor
                @endfor
            </tr>
            @endif
        </thead>
        <tbody>
            <tr>
                <th style="padding: 0;">{{$firstHour}}:00</th>
                <td rowspan="{{$rowCount}}" colspan="{{$dayCount*$itemCount}}" style="padding:0;">
                    {{-- the panel itself --}}
                    <div style="position: relative; height: {{$isPrintVersion ? '650px' : '1000px'}}; margin: 0;">
                        @for($i = 0; $i < $itemCount; ++$i)
                            @php
                            $item = $items[$i];
                            @endphp
                            @foreach($this->blocks[$i] as $block)
                                @php
                                $isReservation = !$block->isFree();
                                $isDisabled = !$isReservation &&
                                                ($item->isOutOfOrder() || $block->getUntil() < \Carbon\Carbon::now());
                                // here, we assume that $from is a midnight date
                                $dayOfWeek = floor($firstDay->diffInDays($block->getFrom()));
                                $startHourFloat = $block->getFrom()->hour + ($block->getFrom()->minute / 60.0);
                                $endHourFloat = $block->getUntil()->isMidnight()
                                                ? 24.0
                                                : ($block->getUntil()->hour + ($block->getUntil()->minute / 60.0));
                                @endphp
                                @if($isReservation)
                                <a href="{{ route('reservations.show', $block->reservation()) }}">
                                @elseif(!$isDisabled && user()->can('requestReservation', $item))
                                {{-- default values as GET request parameters --}}
                                <a href="{{ route('reservations.create', ['item' => $item])
                                            . "?from={$block->getFrom()}&until={$block->getUntil()}"
                                }}">
                                @endif
                                    @php
                                    if ($isReservation) {
                                        $reservation = $block->reservation();
                                        $isOurs = $reservation->user?->is(user());
                                    } else {
                                        $reservation = null;
                                        $isOurs = null;
                                    }

                                    // we have to calculate this relatively to the first and last hours
                                    $top = ($startHourFloat - $firstHour) * 100.0 / $rowCount;
                                    $height = ($endHourFloat - $startHourFloat) * 100.0 / $rowCount;
                                    $display = 'block';
                                    if ($top < 0) {
                                        $height += $top;
                                        $top = 0;
                                        if ($height < 0) $display = 'none';
                                    } else if ($top >= 100.0) {
                                        $display = 'none';
                                    }
                                    if ($top + $height > 100.0) $height = 100.0 - $top;
                                    @endphp
                                    <div style="position: absolute;
                                                left: {{($dayOfWeek * $itemCount + $i) * $columnWidth}}%;
                                                width: {{$columnWidth}}%;
                                                display: {{$display}};
                                                top: {{$top}}%;
                                                height: {{$height}}%;"
                                        @class([
                                            'timetable-block',
                                            'valign-wrapper', 'center-align',
                                            'red' => $isReservation && !$isOurs,
                                            'orange' => $isOurs,
                                            'green' => !$isReservation && !$isDisabled,
                                            'grey' => $isDisabled,
                                            'darken-4' => $isReservation && $reservation->verified
                                                            || !$isReservation && !$isDisabled,
                                            'lighten-2' => $isReservation && !$reservation->verified
                                    ])>
                                        @if(!is_null($reservation))
                                            {{$reservation->displayName()}}
                                            @if($isPrintVersion)
                                                (
                                                    {{ $block->getFrom()->isoFormat('HH:mm') }}
                                                    –
                                                    {{ $block->getUntil()->isoFormat('HH:mm') }}
                                                )
                                            @endif
                                        @endif
                                    </div>
                                @if($isReservation || (!$isDisabled && user()->can('requestReservation', $item)))
                                </a>
                                @endif
                            @endforeach
                        @endfor
                    </div>
                </td>
            </tr>
            @for($hour = $firstHour + 1; $hour < $lastHour; ++$hour)
            <tr>
                <th style="padding: 0;">{{$hour}}:00</th>
            </tr>
            @endfor
        </tbody>
    </table>
</div>