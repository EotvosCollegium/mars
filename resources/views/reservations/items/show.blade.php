@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">{{$item->name}}</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{$item->name}}</span>
                
                @can('requestReservation', $item)
                    <blockquote>
                        @if('washing_machine' == $item->type)
                        @lang('reservations.washing_machine_instructions')
                        @else
                        @lang('reservations.room_instructions')
                        @endif
                    </blockquote>
                    <a href="{{ route('reservations.create', $item) }}" class="btn waves-effect waves-light">
                        @lang('reservations.new_reservation')
                    </a>
                @endcan
                @livewire('timetable', [
                    'items' => [$item]
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
