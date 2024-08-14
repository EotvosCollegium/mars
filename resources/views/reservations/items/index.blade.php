@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('reservations.room_reservations')</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('reservations.room_reservations')</span>

                @livewire('timetable', [
                    'items' => $items->all(),
                    'days' => 1,
                    'displayItemNames' => true
                ])
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('reservations.rooms')</span>

                <blockquote>
                    @lang('reservations.room_index_instructions')
                </blockquote>

                @include('reservations.item_table')
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
