@if(!$onlyInput && !$attributes->get('hidden'))
<div class="input-field col s{{$s}} m{{$m}} l{{$l}} xl{{$xl}}">
@endif
    <textarea
        id="{{$id}}"
        class="materialize-textarea validate @error($id) invalid @enderror"
        {{-- Default values + other provided attributes --}}
        {{$attributes->whereDoesntStartWith('value')->merge([
            'type' => 'text',
            'name' => $id
        ])}}
    >{{ $slot }}</textarea>
    @if(!$attributes->get('hidden') && !$withoutLabel)
    <label for="{{$id}}">{{$label}}</label>
    @endif
    @if($helper ?? null)
    <span class="helper-text">{{ $helper }}</span>
    @endif
    @error($id)
        <span class="helper-text" data-error="{{ $message }}"></span>
    @enderror
@if(!$onlyInput && !$attributes->get('hidden'))
</div>
@endif
