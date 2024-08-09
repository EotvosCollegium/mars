<?php

namespace App\Livewire;

use App\Models\Application;
use App\Models\ApplicationWorkshop;
use Carbon\Carbon;
use Livewire\Component;

class ApplicationWorkshopStatusUpdate extends Component
{
    public Application $application;
    public ApplicationWorkshop $workshop;
    public Carbon $lastUpdated;

    /**
     * Mount the component
     * @param Application $application
     * @param ApplicationWorkshop $workshop
     * @return void
     */
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
        $this->authorize('editStatus', [\App\Models\Application::class, $this->workshop->workshop]);
        $this->application->applicationWorkshops()->where('workshop_id', $workshop)->update(['called_in' => !$this->workshop->called_in]);
        $this->lastUpdated = Carbon::now();
    }

    /**
     * Update the status of the application
     * @param $workshop
     */
    public function admit($workshop)
    {
        $this->authorize('editStatus', [\App\Models\Application::class, $this->workshop->workshop]);
        $this->application->applicationWorkshops()->where('workshop_id', $workshop)->update(['admitted' => !$this->workshop->admitted]);
        $this->lastUpdated = Carbon::now();
    }

    /**
     * $this->updated
     * @return bool
     */
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
        return view('auth.application.workshop_status_update_component');
    }
}
