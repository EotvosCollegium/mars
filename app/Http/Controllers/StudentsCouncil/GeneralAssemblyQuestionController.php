<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Http\Controllers\Controller;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralAssemblyQuestionController extends Controller
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

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'max_options' => 'required|min:1',
            'options' => 'required|array|min:1',
            'options.*' => 'nullable|string|max:255',
        ]);
        $options = array_filter($request->options, function ($s) {
            return $s != null;
        });
        if (count($options) == 0) {
            $validator->after(function ($validator) {
                $validator->errors()->add('options', __('voting.at_least_one_option'));
            });
        }
        $validator->validate();

        $question = $generalAssembly->questions()->create([
            'title' => $request->title,
            'max_options' => $request->max_options,
            'has_long_answers' => false
        ]);
        foreach ($options as $option) {
            $question->options()->create([
                'title' => $option,
                'votes' => 0
            ]);
        }

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

        if ($question->isMultipleChoice()) {
            $validator = Validator::make($request->all(), [
                'option' => 'array|max:' . $question->max_options,
                'option.*' => 'exists:question_options,id',
                'passcode' => 'string'
            ]);
            if (!GeneralAssembly::isTemporaryPasscode($request->passcode)) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('passcode', __('voting.incorrect_passcode'));
                });
            }
            $validator->validate();

            $options = array();
            foreach ($request->option as $oid) {
                $option = QuestionOption::findOrFail($oid);
                if ($option->question_id != $question->id) {
                    abort(401, "Tried to vote for an option which does not belong to the question");
                }
                array_push($options, $option);
            }
            $question->storeAnswers(user(), $options);
        } else {
            $validator = Validator::make($request->all(), [
                'option' => 'exists:question_options,id',
                'passcode' => 'string'
            ]);
            if (!GeneralAssembly::isTemporaryPasscode($request->passcode)) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('passcode', __('voting.incorrect_passcode'));
                });
            }
            $validator->validate();

            $option = QuestionOption::findOrFail($request->option);
            if ($option->question->id != $question->id) {
                abort(401, "Tried to vote for an option which does not belong to the question");
            }
            $question->storeAnswers(user(), $option);
        }

        return redirect()->route('general_assemblies.show', $question->parent)->with('message', __('voting.successful_voting'));
    }
}
