@extends('layouts.app')

@section('title')
<a href="{{route('reservations.items.index', ['type' => $reservation->reservableItem->type])}}"
    class="breadcrumb" style="cursor: pointer">@lang("reservations.{$reservation->reservableItem->type}_reservations")</a>
@if(!$reservation->reservableItem->isWashingMachine())
<a href="{{route('reservations.items.show', $reservation->reservableItem)}}"
  class="breadcrumb" style="cursor: pointer">{{ $reservation->reservableItem->name }}</a>
@endif
@if(!empty($reservation->title))
<a href="#!" class="breadcrumb">{{ $reservation->title }}</a>
@endif
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $reservation->title }}</span>

                <table>
                    <tr>
                        <th>
                            @lang("reservations.{$reservation->reservableItem->type}")
                        </th>
                        <td>{{$reservation->reservableItem->name}}</td>
                    </tr>
                    @if($reservation->title)
                        <tr>
                            <th>@lang('reservations.title')</th>
                            <td>{{$reservation->title}}</td>
                        </tr>
                    @endif
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
                    @if($reservation->reservableItem->isRoom())
                        <tr>
                            <th>@lang('reservations.is_recurring')</th>
                            <td>
                                {{$reservation->isRecurring()
                                    ? ("{$reservation->group->frequency}" . __('reservations.frequency_comment'))
                                    : __('general.no')}}
                            </td>
                        </tr>
                        @can('administer', \App\Models\Reservation::class)
                        <tr>
                            <th>@lang('reservations.is_verified')</th>
                            <td>
                                {{$reservation->verified
                                    ? __('general.yes')
                                    : __('general.no')}}
                            </td>
                        </tr>
                        @endcan
                    @endif
                </table>
            </div>
            @can('modify', $reservation)
                @if(\Carbon\Carbon::make($reservation->reserved_until) >= \Carbon\Carbon::now())
                    <div class="card-action right-align">
                        <a href="{{ route('reservations.edit', $reservation) }}" class="btn waves-effect">
                            @lang('general.edit')
                        </a>
                        @if (!$reservation->verified && user()->can('administer', App\Models\Reservations\Reservation::class))
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
