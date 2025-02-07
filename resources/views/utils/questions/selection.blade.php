@foreach($question->options()->get() as $option)
    @if($question->max_options == 1)
    <x-input.radio :name="$name ?? $question->formKey()" value="{{$option->id}}" text="{{$option->title}}" :checked="old($question->formKey()) == $option->id" />
    @else
    <x-input.checkbox :id="$question->formKey()" :name="$name ?? $question->formKey().'[]'" value="{{$option->id}}" text="{{$option->title}}" :checked="in_array($option->id, (old($question->formKey()) ?? []))" />
    @endif
@endforeach
