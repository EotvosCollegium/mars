<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\Semester;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Utils\HasPeriodicEvent;
use App\Exports\UsersSheets\AnonymousQuestionsExport;

/**
 * Controls actions related to anonymous questions.
 */
class AnonymousQuestionController extends Controller
{
    use HasPeriodicEvent;
    /**
     * This will use the same periodic event as SemesterEvaluationController.
     */
    public function __construct()
    {
        $this->underlyingControllerName =
            \App\Http\Controllers\Secretariat\SemesterEvaluationController::class;
    }

    /**
     * Lists semesters as collapsible cards;
     * containing the export option,
     * the list of questions
     * and the option to add new ones.
     */
    public function indexSemesters()
    {
        $this->authorize('administer', AnswerSheet::class);

        return view('student-council.anonymous-questions.index_semesters');
    }

    /**
     * Returns the 'new question' page.
     */
    public function create(Semester $semester)
    {
        $this->authorize('administer', AnswerSheet::class);

        if ($semester->isClosed()) {
            abort(403, "tried to add a question to a closed semester");
        }
        return view('student-council.anonymous-questions.create', [
            "semester" => $semester
        ]);
    }

    /**
     * Saves a new question.
     */
    public function store(Request $request, Semester $semester)
    {
        $this->authorize('administer', AnswerSheet::class);

        if ($semester->isClosed()) {
            abort(403, "tried to add a question to a closed semester");
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'has_long_answers' => 'nullable|in:on',
            'max_options' => 'exclude_if:has_long_answers,on|required|min:1',
            'options' => 'exclude_if:has_long_answers,on|required|array|min:1',
            'options.*' => 'exclude_if:has_long_answers,on|nullable|string|max:255',
        ]);
        $hasLongAnswers = isset($request->has_long_answers);
        if (!$hasLongAnswers) {
            $options = array_filter($request->options, function ($s) {
                return $s != null;
            });
            if (count($options) == 0) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('options', __('voting.at_least_one_option'));
                });
            }
        }
        $validator->validate();

        $event = $this->periodicEventForSemester($semester);

        $question = $semester->questions()->create([
            'title' => $request->title,
            'max_options' => $hasLongAnswers ? 0 : $request->max_options,
            'has_long_answers' => $hasLongAnswers,
            'opened_at' => $event?->start_date ?? null,
            'closed_at' => $event?->end_date ?? null
        ]);
        if (!$hasLongAnswers) {
            foreach ($options as $option) {
                $question->options()->create([
                    'title' => $option,
                    'votes' => 0
                ]);
            }
        }

        session()->put('section', $semester->id);
        return redirect()->route('anonymous_questions.index_semesters')
                         ->with('message', __('general.successful_modification'));
    }

    /**
     * Returns a page with the options (and results, if authorized) of a question.
     */
    public function show(Semester $semester, Question $question)
    {
        $this->authorize('administer', AnswerSheet::class);

        return view('anonymous_questions.show', [
            "question" => $question
        ]);
    }

    /**
     * Stores the answers given by a user.
     * Handles all questions at once
     * and creates an answer sheet for them.
     */
    public function storeAnswerSheet(Request $request, Semester $semester)
    {
        $this->authorize('is-collegist');

        $validator = Validator::make(
            $request->all(),
            $semester->questionsNotAnsweredBy(user())
                     ->flatMap(fn ($q) => $q->validationRules())
                     ->all()
        );

        // redirect to the correct section
        // we will ignore the 'section' field for now and hard-code it
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->with('section', 'anonymous_questions')
                ->withInput();
        }

        $validatedData = $validator->validated();

        DB::transaction(function () use ($validatedData, $semester) {
            // Since answer sheets are anonymous,
            // we cannot append new answers to the previous sheet (if any);
            // we have to create a new one.
            $answerSheet = AnswerSheet::createForCurrentUser($semester);

            foreach($semester->questionsNotAnsweredBy(user()) as $question) {
                // validation ensures we have answers
                // to all of these questions
                $answer = $validatedData[$question->formKey()];
                if ($question->has_long_answers) {
                    $question->storeAnswers(user(), $answer, $answerSheet);
                } elseif ($question->isMultipleChoice()) {
                    $options = array_map(
                        function (int $id) {return QuestionOption::find($id);},
                        $answer
                    );
                    $question->storeAnswers(user(), $options, $answerSheet);
                } else {
                    $option = QuestionOption::find($answer);
                    $question->storeAnswers(user(), $option, $answerSheet);
                }
            }
        });

        // we will ignore the 'section' field for now and hard-code this
        return back()->with('message', __('general.successful_modification'))->with('section', 'anonymous_questions');
    }

    /**
     * Returns an Excel sheet containing all the answers
     * to the questions of a given semester.
     */
    public function exportAnswerSheets(Semester $semester)
    {
        $this->authorize('administer', AnswerSheet::class);

        return Excel::download(
            new AnonymousQuestionsExport($semester),
            'anonymous_questions_' . $semester->year . '_' . $semester->part . '.xlsx'
        );
    }
}
