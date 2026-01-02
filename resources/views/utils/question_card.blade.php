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
        <x-input.select s="12" onchange="toggleQuestionType()" name="question_type" id="question_type" :elements=\App\Models\Question::QUESTION_TYPES :formatter="fn($t) => __('voting.question_types.' . $t)" text="voting.question_type" required />
    </div>
    <div class="row">
        <x-input.text s="12" type="text" text="voting.question_title" id="title" maxlength="250" required/>
    </div>
    <div class="row" id="voting_options">
        @livewire('parent-child-form', ['title' => __('voting.options'), 'name' => 'options', 'items' => old('options')])
    </div>
    <div class="row">
        <x-input.text type="number" :value="1" id="max_options" text="voting.max_options" required/>
    </div>
</div>
@push('scripts')
{{-- disable answer options if this is checked --}}
<script>
function toggleQuestionType() {
    const question_type = document.getElementById('question_type').value;
    const disable_voting_options = question_type !== "selection" && question_type !== "ranking";
    document
        .querySelectorAll('#voting_options input, #voting_options select, #voting_options textarea, #voting_options button')
        .forEach(el => {
            el.disabled = disable_voting_options;
        });
    document.getElementById('max_options').disabled = disable_voting_options;
}

document.addEventListener('DOMContentLoaded', toggleQuestionType);
</script>
@endpush
