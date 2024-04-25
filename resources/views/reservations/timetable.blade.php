@php
// if we only get one item:
if (isset($item) && !isset($items)) {
    $items = collect([$item]);
}
$cnt = count($items); // the number of items displayed together
// we use the smallest slot size
$slotsize = $items->map(function ($item) {return $item->default_reservation_duration;})->min();
@endphp

<table class="centered">
    <thead>
        <tr>
            {{-- this is for the time column --}}
            <th></th>
            {{-- the seven days of the week --}}
            <th colspan="{{$cnt}}">@lang('reservations.monday')</th>
            <th colspan="{{$cnt}}">@lang('reservations.tuesday')</th>
            <th colspan="{{$cnt}}">@lang('reservations.wednesday')</th>
            <th colspan="{{$cnt}}">@lang('reservations.thursday')</th>
            <th colspan="{{$cnt}}">@lang('reservations.friday')</th>
            <th colspan="{{$cnt}}">@lang('reservations.saturday')</th>
            <th colspan="{{$cnt}}">@lang('reservations.sunday')</th>
        </tr>
    </thead>
    <tbody>
        {{-- as many rows as there are slots in a day --}}
        @for($slot = 0; $slot < ceil(24 * 60 / $slotsize); ++$slot)
            <tr>
                <th>
                    {{$slot * $slotsize}}
                </th>
                @for($day = 0; $day < 7; ++$day)
                    @foreach($items as $item)
                        @php
                            $from = $firstDay->copy()->addDays($day)->addMinutes($slot * $slotsize);
                            $until = $firstDay->copy()->addDays($day)->addMinutes(($slot + 1) * $slotsize);
                            // this is the reservation if the slot is occupied
                            $status = $item->statusOfSlot(
                                // Beware; these have side effects!
                                // Use a copy.
                                $from, $until);
                        @endphp
                        <td @class([
                            'red' => !is_string($status),
                            'green' => $status == 'free',
                            'grey' => $status == 'out_of_order',
                            'darken-4' => !is_string($status) || $status == 'free',
                            'darken-3' => $status == 'out_of_order'
                        ])>
                        {{ is_string($status) ? "" :
                                (is_null($status->user) ? "" : $status->user->name) }}
                        </td>
                    @endforeach
                @endfor
            </tr>
        @endfor
    </tbody>
</table>
