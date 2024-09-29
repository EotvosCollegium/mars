@if(!$onlyInput)
<div class="input-field col s{{$s}} m{{$m}} l{{$l}} xl{{$xl}}">
@endif
    <textarea
        id="{{$id}}"
        {{$attributes->whereDoesntStartWith('value')->merge([
            'name' => $id,
            'class' => "materialize-textarea validate"
        ])}}>{{old($id, $value ?? '')}}</textarea>
    <label for="{{$id}}">{{$label}}</label>
    @if($helper ?? null)
    <span class="helper-text">{{ $helper }}</span>
    @endif
    @error($id)
        <span class="helper-text" data-error="{{ $message }}"></span>
    @enderror
@if(!$onlyInput)
</div>
@endif
