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
        <x-input.select s="12" name="question_type" id="question_type" :elements=\App\Models\Question::QUESTION_TYPES :formatter="fn($t) => __('voting.question_types.' . $t)" text="voting.question_type" required />
    </div>
    <div class="row">
        <x-input.select s="12" name="voting_system" id="voting_system" :elements=\App\Models\Question::VOTING_SYSTEMS text="voting.voting_system" required />
    </div>
    <div class="row">
        <x-input.text s="12" type="text" text="voting.question_title" id="title" maxlength="250" required/>
    </div>
    <div class="row">
        @livewire('parent-child-form', ['title' => __('voting.options'), 'name' => 'options', 'items' => old('options')])
    </div>
    <div class="row">
        <x-input.text type="number" :value="1" id="max_options" text="voting.max_options" required/>
    </div>
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
