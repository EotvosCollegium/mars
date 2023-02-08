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
    public function index()
    {
        $this->authorize('viewAny', Sitting::class);
        return view('student-council.voting.list', [
            "sittings" => Sitting::orderByDesc('opened_at')->get()
        ]);
    }

    public function newSitting()
    {
        $this->authorize('administer', Sitting::class);
        return view('student-council.voting.new_sitting');
    }

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

    public function viewSitting(Sitting $sitting)
    {
        $this->authorize('viewAny', Sitting::class);

        return view('student-council.voting.view_sitting', [
            "sitting" => $sitting
        ]);
    }

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

    public function viewQuestion(Question $question)
    {
        $this->authorize('view_results', $question);
        return view('student-council.voting.view_question', [
            "question" => $question
        ]);
    }

    public function vote(Question $question)
    {
        $this->authorize('vote', $question);
        return view('student-council.voting.vote', [
            "question" => $question
        ]);
    }

    public function saveVote(Question $question, Request $request)
    {
        $this->authorize('vote', $question); //this also checks whether the user has already voted

        if ($question->max_options==1) {
            $validator = Validator::make($request->all(), [
                'option' => 'exists:question_options,id'
            ]);
            $validator->validate();

            $option=QuestionOption::findOrFail($request->option);
            if ($option->question->id!=$question->id) {
                return response()->json(['message' => 'Option not belonging to question'], 403);
            }
            $option->vote(Auth::user());
        } else {
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
        }

        return redirect()->route('voting.view_sitting', $question->sitting)->with('message', __('voting.successful_voting'));
    }
}
