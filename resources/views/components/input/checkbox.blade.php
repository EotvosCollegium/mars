@if(!$onlyInput)
<div class="input-field col s{{$s}} m{{$m}} l{{$l}} xl{{$xl}}"><p>
@endif
<label>
    <input
        type="checkbox"
        {{$attributes->merge([
            'class' => "filled-in checkbox-color",
            'name' => $id
        ])}}
        @checked(in_array($attributes['value'], (old($id)['option'] ?? []))
                 || $checked)
    >
    <span>{{$label}}</span>
</label>
@if(!$onlyInput)
</p></div>
@endif
