<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Voting\Sitting;
use App\Models\Voting\Question;
use App\Models\Voting\QuestionOption;

class VotingController extends Controller
{
    /**
     * Lists sittings.
     */
    public function index()
    {
        $this->authorize('viewAny', Sitting::class);
        return view('student-council.voting.list', [
            "sittings" => Sitting::orderByDesc('opened_at')->get()
        ]);
    }

    /**
     * Returns the 'new sitting' page.
     */
    public function newSitting()
    {
        $this->authorize('administer', Sitting::class);
        return view('student-council.voting.new_sitting');
    }

    /**
     * Saves a new sitting.
     */
    public function addSitting(Request $request)
    {
        $this->authorize('administer', Sitting::class);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);
        $validator->validate();

        $sitting = Sitting::create([
            'title' => $request->title,
            'opened_at' => now(),
        ]);

        return view('student-council.voting.view_sitting', [
            "sitting" => $sitting
        ]);
    }

    /**
     * Returns a page with the details and questions of a sitting.
     */
    public function viewSitting(Sitting $sitting)
    {
        $this->authorize('viewAny', Sitting::class);

        return view('student-council.voting.view_sitting', [
            "sitting" => $sitting
        ]);
    }

    /**
     * Closes a sitting.
     */
    public function closeSitting(Sitting $sitting)
    {
        $this->authorize('administer', Sitting::class);
        if (!$sitting->isOpen()) {
            abort(401, "tried to close a sitting which was not open");
        }
        $sitting->close();
        return back()->with('message', __('voting.sitting_closed'));
    }

    /**
     * Returns the 'new question' page.
     */
    public function newQuestion(Request $request)
    {
        $this->authorize('administer', Sitting::class);

        $validator = Validator::make($request->all(), [
            'sitting' => 'exists:sittings,id',
        ]);
        $validator->validate();
        $sitting = Sitting::findOrFail($request->sitting);

        if (!$sitting->isOpen()) {
            abort(401, "tried to modify a sitting which was not open");
        }
        return view('student-council.voting.new_question', [
            "sitting" => $sitting
        ]);
    }

    /**
     * Saves a new question.
     */
    public function addQuestion(Request $request)
    {
        $this->authorize('administer', Sitting::class);

        $validator = Validator::make($request->all(), [
            'sitting' => 'exists:sittings,id',
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
        $sitting = Sitting::findOrFail($request->sitting);

        if (!$sitting->isOpen()) {
            abort(401, "tried to modify a sitting which was not open");
        }

        $question = $sitting->questions()->create([
            'title' => $request->title,
            'max_options' => $request->max_options,
            'opened_at' => now(),
            'passcode' => \Str::random(8)
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
        $this->authorize('administer', Sitting::class);
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
        $this->authorize('viewAny', Sitting::class);
        return view('student-council.voting.view_question', [
            "question" => $question
        ]);
    }

    /**
     * Returns the voting page.
     */
    public function vote(Question $question)
    {
        $this->authorize('vote', $question);
        return view('student-council.voting.vote', [
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
            if ($request->passcode!=$question->passcode) {
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
            $question->vote(Auth::user(), $options);
        } else {
            $validator = Validator::make($request->all(), [
                'option' => 'exists:question_options,id',
                'passcode' => 'string'
            ]);
            if ($request->passcode!=$question->passcode) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('passcode', __('voting.incorrect_passcode'));
                });
            }
            $validator->validate();

            $option = QuestionOption::findOrFail($request->option);
            if ($option->question->id!=$question->id) {
                abort(401, "Tried to vote for an option which does not belong to the question");
            }
            $question->vote(Auth::user(), array($option));
        }

        return redirect()->route('sittings.show', $question->sitting)->with('message', __('voting.successful_voting'));
    }
}
