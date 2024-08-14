@extends('layouts.app')

@section('title')
<a href="{{route('reservations.items.index')}}" class="breadcrumb" style="cursor: pointer">@lang('reservations.reservations')</a>
@if($reservation->reservableItem->isWashingMachine())
<a href="{{route('reservations.items.index_for_washing_machines')}}"
    class="breadcrumb" style="cursor: pointer">@lang('reservations.washing_reservations')</a>
@else
<a href="{{route('reservations.items.show', $reservation->reservableItem)}}"
  class="breadcrumb" style="cursor: pointer">{{ $reservation->reservableItem->name }}</a>
@endif
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
                        <th>
                            @lang('reservations.'
                                . ($reservation->reservableItem->isWashingMachine() ? 'washing_machine' : 'room')
                            )
                        </th>
                        <td>{{$reservation->reservableItem->name}}</td>
                    </tr>
                    <tr>
                        <th>@lang('reservations.title')</th>
                        <td>{{$reservation->title}}</td>
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
                @if(\Carbon\Carbon::make($reservation->reserved_until) >= \Carbon\Carbon::now())
                    <div class="card-action right-align">
                        <a href="{{ route('reservations.edit', $reservation) }}" class="btn waves-effect">
                            @lang('general.edit')
                        </a>
                        @if (!$reservation->verified && user()->can('administer', App\Models\Reservation::class))
                            <form style="display:inline;" action="{{ route('reservations.verify', $reservation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="waves-effect btn green">@lang('reservations.verify')</button>
                            </form>
                            @if($reservation->isRecurring())
                                <form style="display:inline;" action="{{ route('reservations.verify_all', $reservation->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="waves-effect btn green">@lang('reservations.verify_all')</button>
                                </form>
                            @endif
                        @endif
                        <form style="display:inline;" action="{{ route('reservations.delete', $reservation->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="waves-effect btn red">@lang('general.delete')</button>
                        </form>
                        @if($reservation->isRecurring())
                            <form style="display:inline;" action="{{ route('reservations.delete_all', $reservation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="waves-effect btn red">@lang('general.delete_all')</button>
                            </form>
                        @endif
                    </div>
                @endif
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
