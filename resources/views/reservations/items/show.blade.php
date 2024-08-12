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
                        @lang('reservations.washing_instructions')
                        @else
                        @lang('reservations.room_instructions')
                        @endif
                    </blockquote>
                @endcan

                @can('administer', \App\Models\ReservableItem::class)
                    <div>
                        <form method="POST"
                            action="{{ route('reservations.items.toggle_out_of_order', ['item' => $item]) }}"
                            enctype='multipart/form-data'>
                            @csrf
                            <x-input.button @class([
                                'red' => !$item->out_of_order,
                                'green' => $item->out_of_order
                            ])
                                text="{{'reservations.' . ($item->out_of_order ? 'set_fixed' : 'set_out_of_order')}}" />
                        </form>
                    </div>
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
