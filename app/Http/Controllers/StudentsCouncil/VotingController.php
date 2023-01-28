<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Sitting;

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
        $this->authorize('administer', Sitting::class);

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
        if (!$id->isOpen()) abort(401, "tried to close a sitting which was not open");
        $id->close(); $id->save();
        return back()->with('message', __('voting.sitting_closed'));
    }

    public function newQuestion(Sitting $id)
    {
        $this->authorize('administer', Sitting::class);
        if (!$id->isOpen()) abort(401, "tried to modify a sitting which was not open");
        return view('student-council.voting.new_question', [
            "sitting" => $id
        ]);
    }

    public function addQuestion(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: title, a list of options, a bool about whether it should be opened now, and optionally an opening date
    }

    public function openQuestion(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: question id
    }

    public function closeQuestion(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: question id
    }

    public function updateOptions(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: question id and a list of options
        //should throw if the question has been opened
    }

    public function vote(Request $request)
    {
        $this->authorize('vote', Sitting::class);
        //request contains: option id (we should get the user from somewhere)
        //should throw if the user has already voted or if there are too many options selected
    }
}
