<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Http\Controllers\StudentsCouncil\GeneralAssemblyController;

class Passcode extends Component
{
    /**
     * Returns the passcode.
     */
    public function getPasscodeProperty()
    {
        return app(GeneralAssemblyController::class)->getTemporaryPasscode();
    }

    /**
     * View to render the passcode.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('student-council.general-assemblies.passcode');
    }
}
