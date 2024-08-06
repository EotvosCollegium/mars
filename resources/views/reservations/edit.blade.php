@extends('layouts.app')

@php
if (isset($reservation)) {
    $item = $reservation->reservableItem;
}
@endphp

@section('title')
<a href="{{route('reservations.items.index')}}" class="breadcrumb" style="cursor: pointer">@lang('reservations.reservations')</a>
<a href="{{route('reservations.items.show',
    isset($reservation) ? $reservation->reservableItem : $item)}}"
  class="breadcrumb" style="cursor: pointer">
  {{ isset($reservation) ? $reservation->reservableItem->name : $item->name }}
</a>
<a href="#!" class="breadcrumb">
    {{ isset($reservation) ? $reservation->displayName() : __('reservations.create') }}
</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
            <form action="{{
                isset($reservation)
                ? route('reservations.update', $reservation)
                : route('reservations.store', $item)
            }}" method="POST">
                @csrf
                <div class="card-content">
                    <span class="card-title">
                        @lang(isset($reservation) ? 'reservations.edit' : 'reservations.create')
                    </span>
                    <div class="row">
                        <div s="6">{{ $item->name }}</div>
                        <div s="6">{{ (isset($reservation) && !is_null($reservation->user)) ? $reservation->user->name : '' }}
                    </div>

                    @if('washing_machine' != $item->type)
                        @if(!isset($reservation))
                        <div class="row">
                            <x-input.checkbox s=12 name="recurring" :text="__('reservations.recurring')"
                                onchange="
                                    document.getElementById('frequency').disabled = !this.checked;
                                    document.getElementById('last_day').disabled = !this.checked;
                                "/>
                        </div>
                        <div class="row">
                            <x-input.text disabled s=6 id='frequency' type="number" :text="__('reservations.frequency')" />
                            <x-input.datepicker disabled s=6 id='last_day' :text="__('reservations.last_day')" />
                        </div>
                        @elseif($reservation->isRecurring())

                        <x-input.radio name="for_what" value="this_only" :text="__('reservations.this_only')"
                            onchange="document.getElementById('last_day').disabled = (this.value == 'this_only');" />
                        <x-input.radio name="for_what" value="all_after" :text="__('reservations.all_after')"
                            onchange="document.getElementById('last_day').disabled = (this.value == 'this_only');" />
                        <x-input.radio name="for_what" value="all" :text="__('reservations.all')"
                            onchange="document.getElementById('last_day').disabled = (this.value == 'this_only');" />

                        <div class="row">
                            <x-input.datepicker disabled s=12 id='last_day' :text="__('reservations.last_day')"
                                :value="$reservation->group->last_day" />
                        </div>
                        @endif

                        <div class="row">
                            <x-input.text s="12" type="text" text="reservations.title" id="title"
                                            :value="isset($reservation) ? $reservation->title : ''" maxlength="127"/>
                        </div>
                    @endif {{-- if not washing machine --}}                    

                    <div class="row">
                        <x-input.text  id="reserved_from" type="datetime-local" without-label helper="{{ __('reservations.from') }}"
                                       :value="isset($reservation) ? $reservation->reserved_from : $default_from" required/>

                        {{-- we hide the end date for washing machines --}}
                        @if('washing_machine' != $item->type)
                        <x-input.text  id="reserved_until" type="datetime-local" without-label helper="{{ __('reservations.until') }}"
                                       :value="isset($reservation) ? $reservation->reserved_until : $default_until" required/>
                        @endif
                    </div>
                    <div class="row">
                    <x-input.textarea id="note" text="{{ __('reservations.note') }}"
                                      :value="isset($reservation) ? $reservation->note : ''"/>
                    </div>
                </div>
                <div class="card-action">
                    <div class="row" style="margin:0">
                        <x-input.button text="general.save" class="right"/>
                    </div>
                </div>
            </form>
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
