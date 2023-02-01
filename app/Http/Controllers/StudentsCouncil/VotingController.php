<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Sitting;
use App\Models\Question;
use App\Models\QuestionOption;

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
        //request contains: the title of the new sitting

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

    public function viewSitting(Sitting $id)
    {
        $this->authorize('viewAny', Sitting::class);

        return view('student-council.voting.view_sitting', [
            "sitting" => $id
        ]);
    }

    /*
    public function openSitting(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: sitting id
    }
    */

    public function closeSitting(Sitting $id)
    {
        $this->authorize('administer', Sitting::class);
        if (!$id->isOpen()) {
            abort(401, "tried to close a sitting which was not open");
        }
        $id->close();
        $id->save();
        return back()->with('message', __('voting.sitting_closed'));
    }

    public function newQuestion(Sitting $id)
    {
        $this->authorize('administer', Sitting::class);
        if (!$id->isOpen()) {
            abort(401, "tried to modify a sitting which was not open");
        }
        return view('student-council.voting.new_question', [
            "sitting" => $id
        ]);
    }

    public function addQuestion(Sitting $id, Request $request)
    {
        $this->authorize('administer', Sitting::class);
        if (!$id->isOpen()) {
            abort(401, "tried to modify a sitting which was not open");
        }

        //splitting by newlines and removing options which only have whitespace
        $options=array_map(
            function ($s) {
            return trim($s);
        },
            array_filter(explode("\n", $request->options), function ($s) {
                return !ctype_space($s);
            })
        );
        if (count($options)==0) {
            return back()->with('message', __('voting.at_least_one_option'));
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'max_options' => 'required|min:1'
        ]);
        $validator->validate();

        $question = $id->addQuestion($request->title, $request->max_options, now());
        foreach ($options as $option) {
            $question->addOption($option);
        }

        return redirect()->route('voting.view_question', $question)->with('message', __('general.successful_modification'));
    }

    /*
    public function openQuestion(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: question id
    }
    */

    public function closeQuestion(Question $id)
    {
        $this->authorize('administer', Sitting::class);
        if (!$id->isOpen()) {
            abort(401, "tried to close a question which was not open");
        }
        $id->close();
        $id->save();
        return back()->with('message', __('voting.question_closed'));
    }

    public function viewQuestion(Question $id)
    {
        $this->authorize('view_results', $id);
        return view('student-council.voting.view_question', [
            "question" => $id
        ]);
    }

    /*
    public function updateOptions(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: question id and a list of options
        //should throw if the question has been opened
    }
    */

    public function vote(Question $id)
    {
        $this->authorize('vote', $id);
        return view('student-council.voting.vote', [
            "question" => $id
        ]);
    }

    public function saveVote(Question $id, Request $request)
    {
        $this->authorize('vote', $id); //this also checks whether the user has already voted

        if ($id->max_options==1) {
            $option=QuestionOption::where('id', $request->option)->first();
            $option->vote(Auth::user()); //this also saves
        } else {
            //should throw if there are too many options selected
            if (count($request->option) > $id->max_options) {
                return redirect()->back()->with('message', __('voting.too_many_options'));
            } else {
                foreach ($request->option as $oid) {
                    $option=QuestionOption::where('id', $oid)->first();
                    $option->vote(Auth::user());
                }
            }
        }

        return redirect()->route('voting.view_sitting', $id->sitting())->with('message', __('voting.successful_voting'));
    }
}
