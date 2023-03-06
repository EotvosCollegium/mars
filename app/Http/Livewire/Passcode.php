<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Http\Controllers\StudentsCouncil\VotingController;

class Passcode extends Component
{
    public function getPasscodeProperty()
    {
        return app(VotingController::class)->getTemporaryPasscode();
    }

    public function render()
    {
        return view('student-council.voting.passcode');
    }
}
