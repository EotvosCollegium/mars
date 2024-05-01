@php
$slotsize = $items->map(function (App\Models\ReservableItem $item) {return $item->default_reservation_duration;})
                  ->min();
$cnt = $items->count();
$slotcnt = ceil(24.0 * 60.0 / $slotsize);
@endphp

<table>
    <thead>
        <tr>
            <th></th>
            <th>@lang('reservations.monday')</th>
            <th>@lang('reservations.tuesday')</th>
            <th>@lang('reservations.wednesday')</th>
            <th>@lang('reservations.thursday')</th>
            <th>@lang('reservations.friday')</th>
            <th>@lang('reservations.saturday')</th>
            <th>@lang('reservations.sunday')</th>
        </tr>
    </thead>
    <tbody>
        @for($slot = 0; $slot < $slotcnt; ++$slot)
        <tr>
            <th>{{$firstDay->copy()->addMinutes($slot*$slotsize)->format('h:m')}}</th>
            @for($day = 0; $day < 7; ++$day)
                @php
                $from = $firstDay->copy()->addDays($day)->addMinutes($slot*$slotsize);
                $until = $from->copy()->addMinutes($slotsize);
                @endphp

                @foreach($items as $item)
                    @php
                    $reservations = $item->reservationsInSlot($from, $until);
                    @endphp

                    @if($reservations->empty())
                        @if(is_null($item->out_of_order_from) || $until <= $item->out_of_order_from
                              || (!is_null($item->out_of_order_until) && $from >= $item->out_of_order_until))
                            <td class="green darken-4"></td>
                        @else
                            <td class="grey darken-3"></td>
                        @endif
                    @else
                        <td class="red darken-4">
                            {{ $reservations[0]->displayName() }}
                        </td>
                    @endif
                @endforeach
            @endfor
        </tr>
        @endfor
    </tbody>
</table>