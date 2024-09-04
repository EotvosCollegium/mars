{{-- Belongs to the timetable,
  but has to be defined outside the Livewire component
  because otherwise, re-rendering would break it. --}}
<div class="row">
    <div class="col s12 l4">
        <label for="firstDay" style="font-size: 90%;">@lang('general.date')</label>
        <input
            type="text"
            class="datepicker_firstDay validate"
            id="firstDay"
            value="{{\Carbon\Carbon::today()->format('Y-m-d')}}"
        >
        @push('scripts')
        <script>
            $('.datepicker_firstDay').datepicker({
                format: 'yyyy-mm-dd',
                firstDay: 1,
                showClearBtn: false,
                // this is how we communicate with the Livewire component
                onClose: () => Livewire.dispatch('first-day-updated', {firstDay: $('.datepicker_firstDay').val()})
            });
        </script>
        @endpush
    </div>
</div>