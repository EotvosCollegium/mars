<?php

namespace App\Livewire;

use App\Models\Application;
use App\Models\ApplicationWorkshop;
use Livewire\Component;

class ApplicationStatusUpdate extends Component
{
    public Application $application;
    public ApplicationWorkshop $workshop;

    /**
     * Update the status of the application
     * @param $workshop
     */
    public function callIn($workshop)
    {
        $this->application->applicationWorkshops()->where('workshop_id', $workshop)->update(['called_in' => true]);
        session()->flash('message', 'Státusz frissítve!');
    }

    /**
     * Update the status of the application
     * @param $workshop
     */
    public function admit($workshop)
    {
        $this->application->applicationWorkshops()->where('workshop_id', $workshop)->update(['admitted' => true]);
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
