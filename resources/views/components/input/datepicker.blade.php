@if(!$onlyInput)
<div class="input-field col s{{$s}} m{{$m}} l{{$l}} xl{{$xl}}">
@endif
    <input
        type="text"
        class="datepicker_{{$id}} validate @error($id) invalid @enderror"
        id="{{$id}}"
        onfocus="M.Datepicker.getInstance({{$id}}).open();"
        value="{{old($id) ?? $attributes->get('value')}}"
        {{-- Default values + other provided attributes --}}
        {{$attributes->whereDoesntStartWith('value')->merge([
            'name' => $id
        ])}}
    >
    <label for="{{$id}}">{{$label}}
        @if($attributes->get('asterisk') || $attributes->get('required'))
            <span style="color:red;">*</span>
        @endif</label>
    @error($id)
    <span class="helper-text" data-error="{{ $message }}"></span>
    @enderror
    @if($helper)
    <span class="helper-text">{{ $helper }}</span>
    @endif
@if(!$onlyInput)
</div>
@endif

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.datepicker_{{$id}}').datepicker({
            format: '{{$format}}',
            firstDay: 1,
            yearRange: {{$yearRange}},
            showClearBtn: true,
            @if($attributes->get('max'))
            maxDate: new Date('{{$attributes->get('max')}}'),
            @endif
            @if($attributes->get('min'))
            minDate: new Date('{{$attributes->get('min')}}'),
            @endif
            });
        });
    </script>
@endpush
