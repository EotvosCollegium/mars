<?php

namespace App\Livewire;

use App\Models\ApplicationForm;
use Livewire\Component;

class ApplicationStatusUpdate extends Component
{
    public ApplicationForm $application;

    /**
     * Update the status of the application and flases a confirmation message
     * @param $status
     */
    public function set($status)
    {
        $this->application->update(['status' => $status]);
        session()->flash('message', 'Státusz frissítve!');
    }

    /**
     * Render the component
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('auth.application.status_update_component');
    }
}
