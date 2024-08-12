@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('reservations.washing_reservations')</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('reservations.washing_reservations')</span>

                <blockquote>
                    @lang('reservations.washing_instructions')
                </blockquote>
                
                @include('reservations.item_table')

                @livewire('timetable', [
                    'items' => $items
                ])
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
