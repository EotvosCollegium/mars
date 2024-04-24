@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Ibolya</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<?php
$cnt = count($items); // the number of items displayed together
// we use the smallest slot size
$slotsize = $items->map(function ($item) {return $item->default_reservation_duration;})->min();
?>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Ibolya</span>
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
                                            'gray' => $status == 'out_of_order',
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
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            $('.tooltipped').tooltip();
        });
    </script>
@endpush
