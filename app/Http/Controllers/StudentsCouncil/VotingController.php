<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;

class VotingController extends Controller
{
    public function index()
    {
        $this->authorize('view', Sitting::class);

    }

    public function addSitting(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: title, a bool about whether it should be opened now, and optionally an opening date
    }

    public function openSitting(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: sitting id
    }

    public function closeSitting(Request $request)
    {
        $this->authorize('administer', Sitting::class);
        //request contains: sitting id
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
        //should throw if the user has already voted
    }
}
