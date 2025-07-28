@if(!$onlyInput && !$attributes->get('hidden'))
<div class="input-field col s{{$s}} m{{$m}} l{{$l}} xl{{$xl}}">
@endif
    <input
        id="{{$id}}"
        class="validate @error($id) invalid @enderror"
        value="{{old($id, $value ?? '')}}"
        {{-- Default values + other provided attributes --}}
        {{$attributes->whereDoesntStartWith('value')->merge([
            'type' => 'text',
            'name' => $id
        ])}}
    >
    @if(!$attributes->get('hidden') && !$withoutLabel)
    <label for="{{$id}}">{{$label}}
        @if($attributes->get('asterisk'))
            <span style="color:red;" aria-label="required">*</span>
        @endif
    </label>
    @endif
    @if($helper ?? null)
        <span class="helper-text">
            {{ $helper }}
            @if ($elaboratedHelper)
                <i class="material-icons tooltipped" style="font-size: 1.25em; vertical-align: -0.2em; cursor: default" data-tooltip="{{ $elaboratedHelper }}">info_outline</i>
            @endif
        </span>
    @endif
    @error($id)
        <span class="helper-text" data-error="{{ $message }}"></span>
    @enderror
@if(!$onlyInput && !$attributes->get('hidden'))
</div>
@endif
