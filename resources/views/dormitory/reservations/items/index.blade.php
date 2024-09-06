@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang("reservations.{$type}_reservations")</a>
@endsection

@section('content')

@php
$isForWashingMachine = \App\Enums\ReservableItemType::WASHING_MACHINE->value == $type;
@endphp

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang("reservations.{$type}_reservations")</span>

                @can('requestReservationForType', [
                    \App\Models\Reservations\ReservableItem::class,
                    \App\Enums\ReservableItemType::from($type)
                ])
                    <blockquote>
                        @lang("reservations.{$type}_reservation_instructions")
                    </blockquote>
                @elseif(!config('custom.room_reservation_open'))
                    <blockquote>
                        @lang("reservations.room_reservation_not_open")
                    </blockquote>
                @endif

                @livewire('timetable', [
                    'items' => $items->all(),
                    'days' => $isForWashingMachine ? 3 : 1,
                    'displayItemNames' => !$isForWashingMachine
                ])
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang("reservations.{$type}s")</span>

                <blockquote>
                    @lang("reservations.{$type}_index_instructions")
                </blockquote>

                @include('dormitory.reservations.item_table')
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
