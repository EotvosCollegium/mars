@extends('layouts.app')

{{-- If we are creating a new reservation, $reservation is not set. --}}
@php
if(isset($reservation)) $item = $reservation->reservableItem;
@endphp

@section('title')
<a href="{{route('reservations.items.index')}}" class="breadcrumb" style="cursor: pointer">@lang('reservations.reservations')</a>
<a href="{{route('reservations.items.show', $item)}}"
  class="breadcrumb" style="cursor: pointer">
    {{ $item->name }}
</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form
                action="{{ isset($reservation) ? route('reservations.update', ['reservation' => $reservation])
                                               : route('reservations.store', ['item' => $item]) }}"
                method="POST">
                @csrf

                <div class="card-content">
                    <span class="card-title">@lang('reservations.create_reservation')</span>

                    {{$errors}}

                    <div class="row">
                        <div s="12" m="6">{{ $item->name }}</div>
                        <div s="12" m="6">{{ (isset($reservation) && isset($reservation->user))
                                              ? $reservation->user->name
                                              : ''  }}</div>
                    </div>

                    {{-- For recurring reservations --}}
                    @if ('room' == $item->type)
                        @if(isset($reservation) && $reservation->isRecurring())
                        <div class="row">
                            {{-- this is needed for validation --}}
                            <input type="hidden" name="recurring" value="on"/>

                            <span>@lang('reservations.for_what')</span>
                            <x-input.radio name="for_what" value="this_only" :text="__('reservations.this_only')"
                                onchange="document.getElementById('last_day').disabled = ('this_only' == this.value);"/>
                            <x-input.radio name="for_what" value="all_after" :text="__('reservations.all_after')"
                                onchange="document.getElementById('last_day').disabled = ('this_only' == this.value);"/>
                            <x-input.radio name="for_what" value="all" :text="__('reservations.all')"
                                onchange="document.getElementById('last_day').disabled = ('this_only' == this.value);"/>
                        </div>
                        <div class="row">
                            <x-input.datepicker disabled m="6" id="last_day" type="date-local" without-label :helper="__('reservations.last_day')"
                                        :value="isset($reservation->group) ? $reservation->group->last_day : ''"/>
                        </div>
                        @elseif(!isset($reservation))
                        <div class="row">
                            <x-input.checkbox
                                s="12"
                                only-input
                                id='recurring'
                                :text="__('reservations.recurring')"
                                onchange="
                                    document.getElementById('frequency').disabled = !this.checked;
                                    document.getElementById('last_day').disabled = !this.checked;
                                "
                            />
                        </div>
                        <div class="row">
                            <x-input.text disabled m="6" type="number" text="reservations.frequency"
                                id="frequency" :value="isset($reservation->group) ? $reservation->group->frequency : ''"/>
                            <x-input.datepicker disabled m="6" id="last_day" type="date-local" without-label :helper="__('reservations.last_day')"
                                        :value="isset($reservation->group) ? $reservation->group->last_day : ''"/>
                        </div>
                        @endif

                        <hr />
                    @endif

                    {{-- Further details --}}

                    @if($item->type == 'room')
                    <div class="row">
                        <x-input.text s="12" type="text" text="reservations.title"
                            id="title" :value="isset($reservation) ? $reservation->title : ''"
                            maxlength="127"/>
                    </div>
                    @else
                    <input type="hidden" id="title" name="title" value="" />
                    @endif
                    <div class="row">
                        <x-input.text  m="6" id="reserved_from" type="datetime-local" without-label :helper="__('reservations.from')"
                                       :value="isset($reservation) ? $reservation->reserved_from : ''" required/>
                        <x-input.text  m="6" id="reserved_until" type="datetime-local" without-label :helper="__('reservations.until')"
                                       :value="isset($reservation) ? $reservation->reserved_until : ''" required/>
                    </div>
                    <div class="row">
                    <x-input.textarea s="12" id="note" text="{{ __('reservations.note') }}"
                            value="{{ isset($reservation) ? $reservation->note : '' }}"
                            maxlength="2047"/>
                    </div>
                </div>
                <div class="card-action right-align">
                    <a href="{{ url()->previous() }}" class="waves-effect btn">@lang('general.cancel')</a>
                    <button type="submit" class="waves-effect btn">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
