<?php

namespace App\Livewire;

use App\Models\Application;
use App\Models\ApplicationWorkshop;
use Carbon\Carbon;
use Livewire\Component;

class ApplicationStatusUpdate extends Component
{
    public Application $application;
    public ApplicationWorkshop $workshop;
    public Carbon $lastUpdated;

    public function mount(Application $application, ApplicationWorkshop $workshop)
    {
        $this->application = $application;
        $this->workshop = $workshop;
        $this->lastUpdated = Carbon::now()->subSeconds(2);
    }

    /**
     * Update the status of the application
     * @param $workshop
     */
    public function callIn($workshop)
    {
        $this->application->applicationWorkshops()->where('workshop_id', $workshop)->update(['called_in' => !$this->workshop->called_in]);
        $this->lastUpdated = Carbon::now();
    }

    /**
     * Update the status of the application
     * @param $workshop
     */
    public function admit($workshop)
    {
        $this->application->applicationWorkshops()->where('workshop_id', $workshop)->update(['admitted' => !$this->workshop->admitted]);
        $this->lastUpdated = Carbon::now();
    }

    public function getUpdatedProperty()
    {
        return $this->lastUpdated > Carbon::now()->subSeconds(2);
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
