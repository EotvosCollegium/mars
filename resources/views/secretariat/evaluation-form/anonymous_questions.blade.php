<blockquote>
    TODO

    A névtelen visszajelzéseket nem tároljuk, ezért a mező küldés után üres marad. Ismételt elküldésre nincs szükség.
</blockquote>
<form method="POST" action="{{ route('anonymous_questions.storeAnswers', App\Models\Semester::current()) }}">
    @csrf

    @php
        // We only take the questions that have been answered.
        $questions = App\Models\Semester::current()->questions
            ->filter(function ($question) {return !$question->hasVoted(user());})
    @endphp

    @if ($questions->isEmpty())
    @lang('anonymous_questions.all_questions_filled')
    @else
    @foreach ($questions as $question)
    <div>
        <div class="question-title">{{ $question->title }}</div>
        <div class="row">
            @if($question->has_long_answers)
            <x-input.textarea :id="'q'.$question->id" :text="__('anonymous_questions.long_answer_placeholder')" style="height:100px" />
            @else
            @foreach($question->options()->get() as $option)
                @if($question->max_options==1)
                <x-input.radio :name="'q'.$question->id" value="{{$option->id}}" text="{{$option->title}}" />
                @else
                <x-input.checkbox :name="'q'.$question->id.'[]'" value="{{$option->id}}" text="{{$option->title}}" />
                @endif
            @endforeach
            @endif
            @foreach ($errors->all() as $error)
                <blockquote class="error">{{ $error }}</blockquote>
            @endforeach
        </div>
    </div>
    @endforeach
    <div class="card-action">
        <button type="submit" class="waves-effect btn right">
            @lang('general.save')
        </button>
    </div>
    @endif
</form>
<blockquote>
