<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Http\Controllers\Controller;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GeneralAssemblyQuestionController extends QuestionController
{
    /**
     * Returns the 'new question' page.
     */
    public function create(GeneralAssembly $generalAssembly)
    {
        $this->authorize('administer', GeneralAssembly::class);

        if ($generalAssembly->isClosed()) {
            abort(403, "tried to modify a general_assembly which has been closed");
        }
        return view('student-council.general-assemblies.questions.create', [
            "general_assembly" => $generalAssembly
        ]);
    }

    /**
     * Saves a new question.
     */
    public function store(Request $request, GeneralAssembly $generalAssembly)
    {
        $this->authorize('administer', GeneralAssembly::class);

        if ($generalAssembly->isClosed()) {
            abort(403, "tried to modify a general assembly which has been closed");
        }

        $question = $this->createQuestion($request, $generalAssembly);

        return redirect()->route('general_assemblies.questions.show', [
            "general_assembly" => $generalAssembly,
            "question" => $question,
        ])->with('message', __('general.successful_modification'));
    }

    /**
     * Returns a page with the options (and results, if authorized) of a question.
     */
    public function show(GeneralAssembly $generalAssembly, Question $question)
    {
        $this->authorize('viewAny', GeneralAssembly::class);

        return view('student-council.general-assemblies.questions.show', [
            "question" => $question
        ]);
    }

    /**
     * Opens a question.
     */
    public function openQuestion(GeneralAssembly $generalAssembly, Question $question)
    {
        $this->authorize('administer', GeneralAssembly::class);

        if (!$generalAssembly->isOpen()) {
            abort(403, "tried to open a question when the general assembly itself was not open");
        }
        if ($question->hasBeenOpened()) {
            abort(403, "tried to open a question which has already been opened");
        }
        $question->open();
        return back()->with('message', __('voting.question_opened'));
    }

    /**
     * Closes a question.
     */
    public function closeQuestion(GeneralAssembly $generalAssembly, Question $question)
    {
        $this->authorize('administer', GeneralAssembly::class);

        if (!$question->isOpen()) {
            abort(403, "tried to close a question which was not open");
        }
        $question->close();
        return back()->with('message', __('voting.question_closed'));
    }

    /**
     * Saves a vote.
     */
    public function saveVote(Request $request, GeneralAssembly $generalAssembly, Question $question)
    {
        $this->authorize('vote', $question); //this also checks whether the user has already voted

        $passcode_request = $request->validate(['passcode' => ['required', 'string',
                function($attribute, $value, $fail){
                    if(!GeneralAssembly::isTemporaryPasscode($value)){
                        $fail(__('voting.incorrect_passcode'));
                    }
                }
            ]
        ]);

        $validatedData = $request->validate($question->validationRules());

        $this->saveVoteForQuestion($question, $validatedData);

        return redirect()->route('general_assemblies.show', $question->parent)->with('message', __('voting.successful_voting'));
    }

    public function delete(GeneralAssembly $generalAssembly, Question $question){
        $this->authorize('administer', GeneralAssembly::class);

        if($question['parent_type'] != GeneralAssembly::class){
            abort(400);
        }

        $question->delete();

        return redirect(route('general_assemblies.show', ["general_assembly" => $generalAssembly]));
    }
}
