<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\GeneralAssemblies\Question;
use App\Models\GeneralAssemblies\QuestionOption;

class GeneralAssemblyController extends Controller
{
    /**
     * Lists general_assemblies.
     */
    public function index()
    {
        $this->authorize('viewAny', GeneralAssembly::class);
        return view('student-council.general-assemblies.list', [
            "general_assemblies" => GeneralAssembly::orderByDesc('opened_at')->get()
        ]);
    }

    /**
     * Returns the 'new GeneralAssembly' page.
     */
    public function create()
    {
        $this->authorize('administer', GeneralAssembly::class);
        return view('student-council.general-assemblies.new_sitting');
    }

    /**
     * Saves a new GeneralAssembly.
     */
    public function store(Request $request)
    {
        $this->authorize('administer', GeneralAssembly::class);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);
        $validator->validate();

        $general_assembly = GeneralAssembly::create([
            'title' => $request->title,
            'opened_at' => now(),
        ]);

        return view('student-council.general-assemblies.view_sitting', [
            "general_assembly" => $general_assembly,
            "passcode" => self::getTemporaryPasscode()
        ]);
    }

    /**
     * Returns a page with the details and questions of a general_assembly.
     */
    public function show(GeneralAssembly $general_assembly)
    {
        $this->authorize('viewAny', GeneralAssembly::class);

        return view('student-council.general-assemblies.view_sitting', [
            "general_assembly" => $general_assembly,
            "passcode" => self::getTemporaryPasscode()
        ]);
    }

    /**
     * Closes a general_assembly.
     */
    public function closeAssembly(GeneralAssembly $general_assembly)
    {
        $this->authorize('administer', GeneralAssembly::class);
        if (!$general_assembly->isOpen()) {
            abort(401, "tried to close a general_assembly which was not open");
        }
        $general_assembly->close();
        return back()->with('message', __('voting.sitting_closed'));
    }

    /**
     * Returns the 'new question' page.
     */
    public function newQuestion(Request $request)
    {
        $this->authorize('administer', GeneralAssembly::class);

        $validator = Validator::make($request->all(), [
            'general_assembly' => 'exists:general_assemblies,id',
        ]);
        $validator->validate();
        $general_assembly = GeneralAssembly::findOrFail($request->general_assembly);

        if (!$general_assembly->isOpen()) {
            abort(401, "tried to modify a general_assembly which was not open");
        }
        return view('student-council.general-assemblies.new_question', [
            "general_assembly" => $general_assembly
        ]);
    }

    /**
     * Saves a new question.
     */
    public function addQuestion(Request $request)
    {
        $this->authorize('administer', GeneralAssembly::class);

        $validator = Validator::make($request->all(), [
            'general_assembly' => 'exists:general_assemblies,id',
            'title' => 'required|string',
            'max_options' => 'required|min:1',
            'options' => 'required|array|min:1',
            'options.*' => 'nullable|string|max:255',
        ]);
        $options = array_filter($request->options, function ($s) {
            return $s != null;
        });
        if (count($options)==0) {
            $validator->after(function ($validator) {
                $validator->errors()->add('options', __('voting.at_least_one_option'));
            });
        }
        $validator->validate();
        $general_assembly = GeneralAssembly::findOrFail($request->general_assembly);

        if (!$general_assembly->isOpen()) {
            abort(401, "tried to modify a general assembly which was not open");
        }

        $question = $general_assembly->questions()->create([
            'title' => $request->title,
            'max_options' => $request->max_options,
            'opened_at' => now()
        ]);
        foreach ($options as $option) {
            $question->options()->create([
                'title' => $option,
                'votes' => 0
            ]);
        }

        return redirect()->route('questions.show', $question)->with('message', __('general.successful_modification'));
    }

    /**
     * Closes a question.
     */
    public function closeQuestion(Question $question)
    {
        $this->authorize('administer', GeneralAssembly::class);
        if (!$question->isOpen()) {
            abort(401, "tried to close a question which was not open");
        }
        $question->close();
        return back()->with('message', __('voting.question_closed'));
    }

    /**
     * Returns a page with the options (and results, if authorized) of a question.
     */
    public function viewQuestion(Question $question)
    {
        $this->authorize('viewAny', GeneralAssembly::class);
        return view('student-council.general-assemblies.view_question', [
            "question" => $question
        ]);
    }

    /**
     * Saves a vote.
     */
    public function saveVote(Question $question, Request $request)
    {
        $this->authorize('vote', $question); //this also checks whether the user has already voted

        if ($question->isMultipleChoice()) {
            $validator = Validator::make($request->all(), [
                'option' => 'array|max:'.$question->max_options,
                'option.*' => 'exists:question_options,id',
                'passcode' => 'string'
            ]);
            if (!self::isTemporaryPasscode($request->passcode)) {
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
            $question->vote(user(), $options);
        } else {
            $validator = Validator::make($request->all(), [
                'option' => 'exists:question_options,id',
                'passcode' => 'string'
            ]);
            if (!self::isTemporaryPasscode($request->passcode)) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('passcode', __('voting.incorrect_passcode'));
                });
            }
            $validator->validate();

            $option = QuestionOption::findOrFail($request->option);
            if ($option->question->id!=$question->id) {
                abort(401, "Tried to vote for an option which does not belong to the question");
            }
            $question->vote(user(), array($option));
        }

        return redirect()->route('general_assemblies.show', $question->generalAssembly)->with('message', __('voting.successful_voting'));
    }

    /**
     * Returns a random 6 char string, refreshed every minute.
     */
    public static function getTemporaryPasscode($offset = "0 minute"): string
    {
        return substr(hash('sha256', date('Y-m-d H:i', strtotime($offset))), 0, 6);
    }

    /**
     * Decides if a value matches the current temporary password.
     * The previous password is also accepted.
     */
    public static function isTemporaryPasscode(string $value): bool
    {
        return $value == self::getTemporaryPasscode()
            || $value == self::getTemporaryPasscode('-1 minute');
    }
}
