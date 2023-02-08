<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
        $sitting->save();
        return back()->with('message', __('voting.sitting_closed'));
    }

    /**
     * Returns the 'new question' page.
     */
    public function newQuestion(Sitting $sitting)
    {
        $this->authorize('administer', Sitting::class);
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
    public function addQuestion(Sitting $sitting, Request $request)
    {
        $this->authorize('administer', Sitting::class);
        if (!$sitting->isOpen()) {
            abort(401, "tried to modify a sitting which was not open");
        }

        //splitting by newlines and removing options which only have whitespace
        $options=array_map(
            function ($s) {
                return trim($s);
            },
            array_filter(explode("\n", $request->options), function ($s) {
                return $s!="" && !ctype_space($s); //ctype_space would give false for ""
            })
        );

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'max_options' => 'required|min:1'
        ]);
        if (count($options)==0) {
            $validator->after(function ($validator) {
                $validator->errors()->add('options', __('voting.at_least_one_option', ['attribute' => 'options']));
            });
        }
        $validator->validate();

        $question = $sitting->questions()->create([
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

        return redirect()->route('voting.view_question', $question)->with('message', __('general.successful_modification'));
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
        $question->save();
        return back()->with('message', __('voting.question_closed'));
    }

    /**
     * Returns a page with the options (and results, if authorized) of a question.
     */
    public function viewQuestion(Question $question)
    {
        $this->authorize('viewResults', $question);
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
                'option.*' => 'exists:question_options,id'
            ]);
            $validator->validate();

            foreach ($request->option as $oid) {
                $option=QuestionOption::findOrFail($oid);
                if ($option->question->id!=$question->id) {
                    return response()->json(['message' => 'Option not belonging to question'], 403);
                }
                $option->vote(Auth::user());
            }
        } else {
            $validator = Validator::make($request->all(), [
                'option' => 'exists:question_options,id'
            ]);
            $validator->validate();

            $option=QuestionOption::findOrFail($request->option);
            if ($option->question->id!=$question->id) {
                return response()->json(['message' => 'Option not belonging to question'], 403);
            }
            $option->vote(Auth::user());
        }

        return redirect()->route('voting.view_sitting', $question->sitting)->with('message', __('voting.successful_voting'));
    }
}
