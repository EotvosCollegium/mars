<blockquote>
    @lang('anonymous_questions.information_text')
</blockquote>
<form method="POST" action="{{ route('anonymous_questions.store_answer_sheet', $periodicEvent->semester) }}">
    @csrf
    <input type="hidden" name="section" value="anonymous_questions">

    @php
        // We only take the questions that have been answered.
        $questions = $periodicEvent->semester->questionsNotAnsweredBy(user());
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
                @if($question->question_type == \App\Models\Question::TEXT_ANSWER)
                @include('utils.questions.text_answer', ['question' => $question])
                @elseif($question->question_type == \App\Models\Question::SELECTION)
                @include('utils.questions.selection', ['question' => $question])
                @elseif($question->question_type == \App\Models\Question::RANKING)
                @include('utils.questions.ranking', ['question' => $question])
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
