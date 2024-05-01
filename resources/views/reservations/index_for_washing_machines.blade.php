@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('reservations.washing_machines')</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('reservations.washing_machines')</span>
                @include('reservations.timetable')
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
