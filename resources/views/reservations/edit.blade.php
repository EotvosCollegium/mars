@extends('layouts.app')

@php
if (isset($reservation)) {
    $item = $reservation->reservableItem;
}
@endphp

@section('title')
<a href="{{route('reservations.items.index')}}" class="breadcrumb" style="cursor: pointer">@lang('reservations.reservations')</a>
@if($item->isWashingMachine())
<a href="{{route('reservations.items.index_for_washing_machines')}}"
    class="breadcrumb" style="cursor: pointer">@lang('reservations.washing_reservations')</a>
@else
<a href="{{route('reservations.items.show', $item)}}"
  class="breadcrumb" style="cursor: pointer">
  {{ isset($reservation) ? $reservation->reservableItem->name : $item->name }}
</a>
@endif
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
                        ({{ $item->name }})
                    </span>
                    <blockquote>
                        @lang('reservations.one_hour_slot_only')
                    </blockquote>

                    @if(!$item->isWashingMachine())
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
                                            :value="isset($reservation) ? $reservation->title : ''" maxlength="127"/>
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
