@extends('layouts.app')

@php
if (isset($reservation)) {
    $item = $reservation->reservableItem;
}
@endphp

@section('title')
<a href="{{route('reservations.items.index', ['type' => $item->type])}}"
    class="breadcrumb" style="cursor: pointer">@lang("reservations.{$item->type}_reservations")</a>
<a href="{{route('reservations.items.show', $item)}}"
  class="breadcrumb" style="cursor: pointer">
  {{ isset($reservation) ? $reservation->reservableItem->name : $item->name }}
</a>
@if(!isset($reservation) || !empty($reservation->title))
<a href="#!" class="breadcrumb">
    {{ isset($reservation) ? $reservation->title : __('reservations.create') }}
</a>
@endif
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
                        @if(isset($reservation))
                            @lang('reservations.edit')
                        @else
                            @lang('reservations.create')
                            ({{ $item->name }})
                        @endif
                    </span>

                    @if($item->isWashingMachine())
                        <blockquote>
                            @lang('reservations.one_hour_slot_only')
                        </blockquote>
                    @else
                        @if(!isset($reservation))
                        <div class="row">
                            <x-input.checkbox s=12 name="recurring" :text="__('reservations.recurring')"
                                onchange="
                                    document.getElementById('frequency').disabled = !this.checked;
                                    document.getElementById('last_day').disabled = !this.checked;
                                "/>
                        </div>
                        <div class="row">
                            @if('on' == old('recurring'))
                            <x-input.text s=6 id='frequency' type="number" :text="__('reservations.frequency')" />
                            <x-input.datepicker s=6 id='last_day' :text="__('reservations.last_day')" />
                            @else
                            <x-input.text disabled s=6 id='frequency' type="number" :text="__('reservations.frequency')" />
                            <x-input.datepicker disabled s=6 id='last_day' :text="__('reservations.last_day')" />
                            @endif
                        </div>
                        @elseif($reservation->isRecurring())

                        <x-input.radio name="for_what" value="edit_this_only" :text="__('reservations.edit_this_only')"
                            onchange="document.getElementById('last_day').disabled = (this.value == 'edit_this_only');" />
                        <x-input.radio name="for_what" value="edit_all_after" :text="__('reservations.edit_all_after')"
                            onchange="document.getElementById('last_day').disabled = (this.value == 'edit_this_only');" />
                        <x-input.radio name="for_what" value="edit_all" :text="__('reservations.edit_all')"
                            onchange="document.getElementById('last_day').disabled = (this.value == 'edit_this_only');" />

                        <div class="row">
                            <x-input.datepicker disabled s=12 id='last_day' :text="__('reservations.last_day')"
                                :value="$reservation->group->last_day" />
                        </div>
                        @endif

                        <div class="row">
                            <x-input.text s="12" type="text" text="reservations.title" id="title"
                                            :value="isset($reservation) ? $reservation->title : old('title')" maxlength="127"/>
                        </div>
                    @endif {{-- if not washing machine --}}                    

                    <div class="row">
                        <x-input.text  id="reserved_from" type="datetime-local" without-label helper="{{ __('reservations.from') }}"
                                       :value="isset($reservation) ? $reservation->reserved_from :
                                               (old('reserved_from') ?? $group_from)"
                                       {{-- only integer hours should be given for washing machines --}}
                                       :step="$item->isWashingMachine() ? 3600 : 60"
                                       required/>

                        {{-- we hide the end date for washing machines --}}
                        @if(!$item->isWashingMachine())
                        <x-input.text  id="reserved_until" type="datetime-local" without-label helper="{{ __('reservations.until') }}"
                                       :value="isset($reservation) ? $reservation->reserved_until :
                                               (old('reserved_until') ?? $group_until)"
                                       required/>
                        @endif
                    </div>
                    <div class="row">
                        <blockquote>@lang('reservations.note_disclaimer')</blockquote>
                        <x-input.textarea id="note" text="{{ __('reservations.note') }}"
                                        :value="isset($reservation) ? $reservation->note : old('note')"/>
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
