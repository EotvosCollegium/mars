<?php

namespace App\Http\Controllers\Secretariat;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\Semester;
use App\Models\GeneralAssemblies\Question;

/**
 * Controls actions related to anonymous questions.
 */
class AnonymousQuestionController extends Controller
{
    /**
     * Returns the 'new question' page.
     */
    public function create(Semester $semester)
    {
        $this->authorize('administer', AnswerSheet::class);

        if ($semester->isClosed()) {
            abort(403, "tried to add a question to a closed semester");
        }
        return view('student-council.general-assemblies.questions.create', [
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

        $question = $semester->questions()->create([
            'title' => $request->title,
            'max_options' => $hasLongAnswers ? 0 : $request->max_options,
            'has_long_answers' => $hasLongAnswers
        ]);
        if (!$hasLongAnswers) foreach ($options as $option) {
            $question->options()->create([
                'title' => $option,
                'votes' => 0
            ]);
        }

        return redirect()->route('anonymous_questions.show', [
            "semester" => $semester,
            "question" => $question,
        ])->with('message', __('general.successful_modification'));
    }

    /**
     * Returns a page with the options (and results, if authorized) of a question.
     */
    public function show(Semester $semester, Question $question)
    {
        $this->authorize('administer', AnswerSheet::class);
        // check whether it really belongs here
        // and throw a 404 if not
        if ($semester != $question->parent) abort(404);
        return view('anonymous_questions.show', [
            "question" => $question
        ]);
    }
}
