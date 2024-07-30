{{--
    The content of a form to create an App\Models\Question.
    Expects a boolean named $canHaveLongAnswers.
--}}
<div class="card-content">
    @foreach ($errors->all() as $error)
    <blockquote class="error">{{ $error }}</blockquote>
    @endforeach

    <span class="card-title">@lang('voting.new_question')</span>
    <div class="row">
        <x-input.text s="12" type="text" text="voting.question_title" id="title" maxlength="250" required/>
    </div>
    <div class="row">
        @livewire('parent-child-form', ['title' => __('voting.options'), 'name' => 'options', 'items' => old('options')])
    </div>
    <div class="row">
        <x-input.text type="number" :value="1" id="max_options" text="voting.max_options" required/>
    </div>
    @if ($canHaveLongAnswers)
    <div class="row">
        <x-input.checkbox s="12" name="has_long_answers" text="anonymous_questions.has_long_answers"
            onchange="toggleLongAnswers(this);"/>
    </div>
    @endif
</div>
@push('scripts')
{{-- disable answer options if this is checked --}}
<script>
function toggleLongAnswers(checkbox) {
    document.getElementById('max_options').disabled = checkbox.checked;
    const toDisable = document.getElementsByClassName('parent-child');
    for (let i = 0; i < toDisable.length; i++) {
        toDisable[i].disabled = checkbox.checked;
    }
}
</script>
@endpush
