@extends('layouts.app')

@section('title')
<a href="{{route('reservations.items.index')}}" class="breadcrumb" style="cursor: pointer">@lang('reservations.reservations')</a>
<a href="{{route('reservations.items.show', $reservation->reservableItem)}}"
  class="breadcrumb" style="cursor: pointer">{{ $reservation->reservableItem->name }}</a>
<a href="#!" class="breadcrumb">{{ $reservation->displayName() }}</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $reservation->displayName() }}</span>

                <table>
                    <tr>
                        <th>@lang('reservations.item')</th>
                        <td>{{$reservation->reservableItem->name}}</td>
                    </tr>
                    <tr>
                        <th>@lang('general.user')</th>
                        <td>{{is_null($reservation->user) ? "" : $reservation->user->name}}</td>
                    </tr>
                    <tr>
                        <th>@lang('reservations.from')</th>
                        <td>{{$reservation->reserved_from}}</td>
                    </tr>
                    <tr>
                        <th>@lang('reservations.until')</th>
                        <td>{{$reservation->reserved_until}}</td>
                    </tr>
                    <tr>
                        <th>@lang('general.note')</th>
                        <td>{{$reservation->note}}</td>
                    </tr>
                </table>
            </div>
            @can('modify', $reservation)
            <div class="card-action right-align">
                <a href="{{ route('reservations.edit', $reservation) }}" class="btn waves-effect">
                    @lang('general.edit')
                </a>
                @if (!$reservation->verified && user()->can('administer', App\Models\Reservation::class))
                <form action="{{ route('reservations.verify', $reservation->id) }}" method="POST">
                    @csrf
                    <x-input.button text="reservations.verify" class="green" />
                </form>
                    @if($reservation->isRecurring())
                    <form action="{{ route('reservations.verify_all', $reservation->id) }}" method="POST">
                        @csrf
                        <x-input.button text="reservations.verify_all" class="green" />
                    </form>
                    @endif
                @endif
                <form action="{{ route('reservations.delete', $reservation->id) }}" method="POST">
                    @csrf
                    <x-input.button text="general.delete" class="red" />
                </form>
                @if($reservation->isRecurring())
                <form action="{{ route('reservations.delete_all', $reservation->id) }}" method="POST">
                    @csrf
                    <x-input.button text="reservations.delete_all" class="red" />
                </form>
                @endif
            </div>
            @endcan
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
