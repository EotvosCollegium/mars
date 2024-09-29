@if(!$onlyInput)
<div class="input-field col s{{$s}} m{{$m}} l{{$l}} xl{{$xl}}">
@endif
    <select
        searchable="@lang('general.search')"
        id="{{ $id }}"
        {{-- Required is not supported because the select does not support validation --}}
        {{$attributes->whereDoesntStartWith('required')->merge([
            'name' => $id
        ])}}
        >
        @if(!$withoutPlaceholder && count($elements) != 1)
        <option
            value=""
            disabled="true"
            selected="true">{{ $placeholder ?? __('general.choose_option') }}
        </option>
        @endif
        @if($allowEmpty)
        <option value=''>{{is_string($allowEmpty) ? $allowEmpty : ""}}</option>
        @endif
        @php
        $value = (old($id) ?? $attributes->get('value')) ?? $default;
        @endphp
        @foreach ($elements as $element)
            <option
                value="{{ $element->id ?? $element }}"
                @selected(($element->id ?? ($element->name ?? $element)) == $value)
                >{{$formatter($element)}}</option>
        @endforeach
    </select>
    @if(!$withoutLabel)
    <label for="{{$id}}">{{$label}}</label>
    @endif
    @if($helper ?? null)
    <span class="helper-text">{{ $helper }}</span>
    @endif
    @error($attributes->get('value') && $attributes->get('value') != null)
        <span class="helper-text red-text">{{ $message }}</span>
    @enderror
    @error($id)
        <span class="helper-text red-text">{{ $message }}</span>
    @enderror
@if(!$onlyInput)
</div>
@endif
