<blockquote>
    @lang('anonymous_questions.information_text')
</blockquote>
<form method="POST" action="{{ route('anonymous_questions.store_answer_sheet', App\Models\Semester::current()) }}">
    @csrf
    <input type="hidden" name="section" value="anonymous_questions">

    @php
        // We only take the questions that have been answered.
        $questions = App\Models\Semester::current()->questionsNotAnsweredBy(user());
    @endphp

    @if ($questions->isEmpty())
    @lang('anonymous_questions.all_questions_filled')
    @else

    <ul class="collection">
        @foreach ($errors->all() as $error)
        <blockquote class="error">{{ $error }}</blockquote>
        @endforeach

        @foreach ($questions as $question)
        <li class="collection-item">
            <div class="question-title">{{ $question->title }}</div>
            <div class="row">
                @if($question->has_long_answers)
                <x-input.textarea :id="$question->formKey()" :text="__('anonymous_questions.long_answer_placeholder')" style="height:100px" />
                @else
                @foreach($question->options()->get() as $option)
                    @if($question->max_options==1)
                    <x-input.radio :name="$question->formKey()" value="{{$option->id}}" text="{{$option->title}}"
                        :checked="old($question->formKey()) == $option->id" />
                    @else
                    <x-input.checkbox :id="$question->formKey()" :name="$question->formKey().'[]'" value="{{$option->id}}" text="{{$option->title}}"
                        {{-- for some reason, having a custom id changes the structure of old($id) --}}
                        :checked="in_array($option->id, (old($question->formKey()) ?? []))" />
                    @endif
                @endforeach
                @endif
            </div>
        </li>
        @endforeach
    </ul>

    <div class="card-action">
        <button type="submit" class="waves-effect btn right">
            @lang('general.save')
        </button>
    </div>

    @endif
</form>
<blockquote>
