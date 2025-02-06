<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
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

        $validatedData = $request->validate([
            'title' => 'required|string',
            'question_type' => [
                'required',
                Rule::in(Question::QUESTION_TYPES)
            ],
            'max_options' => ['required', 'min:1', Rule::excludeIf($request['question_type'] != 'selection')],
            'options' => ['required', 'min:1', Rule::excludeIf($request['question_type'] != 'selection'), 'array'],
            'options.*' => ['required', 'min:1', 'max:255', Rule::excludeIf($request['question_type'] != 'selection'), 'string'],
        ]);
        if ($validatedData['question_type'] == Question::SELECTION) {
            $options = array_filter($validatedData['options'], function ($s) {
                return $s != null;
            });
            if (count($options) == 0) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('options', __('voting.at_least_one_option'));
                });
            }
        }

        $event = $this->periodicEventForSemester($semester);

        $question = $semester->questions()->create([
            'title' => $validatedData['title'],
            'max_options' => $validatedData['question_type'] == Question::SELECTION ? $validatedData['max_options'] : null,
            'question_type' => $validatedData['question_type'],
            'opened_at' => $event?->start_date ?? null,
            'closed_at' => $event?->end_date ?? null
        ]);
        if ($validatedData['question_type'] == Question::SELECTION) {
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

            foreach ($semester->questionsNotAnsweredBy(user()) as $question) {
                // validation ensures we have answers
                // to all of these questions
                $answer = $validatedData[$question->formKey()];
                if ($question->question_type == Question::TEXT_ANSWER) {
                    $question->storeAnswers(user(), $answer, $answerSheet);
                } elseif ($question->question_type == Question::SELECTION) {
                    if ($question->isMultipleChoice()) {
                        $options = array_map(
                            function (int $id) {return QuestionOption::find($id);},
                            $answer
                        );
                        $question->storeAnswers(user(), $options, $answerSheet);
                    } else {
                        $option = QuestionOption::find($answer);
                        $question->storeAnswers(user(), $option, $answerSheet);
                    }
                } else {
                    throw new \Exception("Unknown question type");
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
