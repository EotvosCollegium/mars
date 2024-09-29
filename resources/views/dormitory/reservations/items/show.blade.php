@extends('layouts.app')

@section('title')
<a href="{{route('reservations.items.index', ['type' => $item->type])}}"
    class="breadcrumb" style="cursor: pointer">@lang("reservations.{$item->type}_reservations")</a>
<a href="#!"
    class="breadcrumb" style="cursor: pointer">{{ $item->name }}</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{$item->name}}</span>
                
                @can('requestReservation', $item)
                    <blockquote>
                        @lang("reservations.{$item->type}_reservation_instructions")
                    </blockquote>
                @elseif($item->isRoom() && !config('custom.room_reservation_open'))
                    <blockquote>
                        @lang("reservations.room_reservation_not_open")
                    </blockquote>
                @endif

                <div>
                    <form method="POST"
                        action="{{
                            route(
                                user()->can('administer', \App\Models\Reservations\ReservableItem::class)
                                ? 'reservations.items.toggle_out_of_order' : 'reservations.items.report_fault',
                                ['item' => $item]
                            )
                            }}"
                        enctype='multipart/form-data'>
                        @csrf
                        <x-input.button @class([
                            'red' => !$item->out_of_order,
                            'green' => $item->out_of_order
                        ])
                            text="{{'reservations.' . (
                                user()->can('administer', \App\Models\Reservations\ReservableItem::class)
                                ? ($item->out_of_order ? 'set_fixed' : 'set_out_of_order')
                                : ($item->out_of_order ? 'report_fix' : 'report_fault')
                            )}}"
                        />
                    </form>
                </div>

                @livewire('timetable', [
                    'items' => [$item],
                    'days' => 3
                ])
            </div>
            <div class="card-action">
                <x-input.button floating href="{{ route('reservations.items.show_print_version', $item) }}" icon="print" class="right" />
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
