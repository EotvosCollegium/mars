@php
    $inputId = $id . '_autocomplete';
    $value = old($id) ?? $attributes->get('value') ?? $default;
    $formattedValue = optional(collect($elements)->firstWhere('id', $value), fn($e) => $formatter($e)) ?? '';
@endphp

@if(!$onlyInput)
<div class="input-field col s{{ $s }} m{{ $m }} l{{ $l }} xl{{ $xl }}">
@endif

    <div style="position:relative;">
        <input
            type="text"
            id="{{ $inputId }}"
            autocomplete="off"
            class="selectize-autocomplete
            @error($id)
                input-invalid
            @enderror
            "
            value="{{ $formattedValue }}"
            style="padding-right:2.5rem;"
            @unless($allowEmpty)
                required
            @endunless
        >
    </div>

    <input type="hidden" id="{{ $id }}" value="{{ $value }}"
        {{$attributes->whereDoesntStartWith('required')->merge([
            'name' => $id
        ])}}
    >

    @unless($withoutLabel)
        <label for="{{ $inputId }}" class="active" id="{{ $inputId }}_label">{{ $label }}@if($attributes->get('asterisk'))
            <span style="color:red;" aria-label="required">*</span>
        @endif</label>
    @endunless

    @isset($helper)
        <span class="helper-text">{{ $helper }}</span>
    @endisset
    
    @error($id)
        <span class="helper-text red-text">{{ $message }}</span>
    @enderror

@if(!$onlyInput)
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputId = '{{ $inputId }}';
    const hiddenId = '{{ $id }}';
    const arrowId = '{{ $id }}_arrow_icon';
    const labelId = inputId + '_label';

    const inputEl = document.getElementById(inputId);
    const hiddenInput = document.getElementById(hiddenId);
    const arrowIcon = document.getElementById(arrowId);
    const labelEl = document.getElementById(labelId);

    // Prepare options for Selectize
    const options = [
        @if($allowEmpty)
            { value: '', text: '' },
        @endif
        @foreach ($elements as $element)
            {
                value: "{{ $element->id ?? $element }}",
                text: "{{ $formatter($element) }}"
            },
        @endforeach
    ];

    // Initialize Selectize
    const selectizeInstance = $(inputEl).selectize({
        options: options,
        valueField: 'value',
        labelField: 'text',
        searchField: 'text',
        create: false,
        maxItems: 1,
        placeholder: "{{ !$withoutPlaceholder && count($elements) !== 1 ? ($placeholder ?? __('general.choose_option')) : '' }}",
        onChange(val) {
            hiddenInput.value = val || '';
        },
    })[0].selectize;

    selectizeInstance.$control_input.on('focus', function(e) {
        console.log("focused");
        selectizeInstance.clear();
        hiddenInput.value = '';
    });

    // Set initial value
    selectizeInstance.setValue(hiddenInput.value);

    window["toggleSelectizeDropdown_{{ $id }}"] = () => {
        if (selectizeInstance.isOpen) {
            selectizeInstance.close();
        } else {
            selectizeInstance.open();
            selectizeInstance.focus();
        }
    };
});
</script>
@endpush