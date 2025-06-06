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
class AnonymousQuestionController extends QuestionController
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

        $question = $this->createQuestion($request, $semester);

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
                $this->saveVoteForQuestion($question, $validatedData, $answerSheet);
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

    public function delete(Semester $semester, Question $question)
    {
        $this->authorize('administer', AnswerSheet::class);

        if ($question['parent_type'] != Semester::class) {
            abort(400);
        }

        $question->delete();

        return redirect(route('anonymous_questions.index_semesters'));
    }
}
