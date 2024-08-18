<?php

namespace App\Livewire;

use App\Models\Application;
use App\Models\ApplicationWorkshop;
use Carbon\Carbon;
use Livewire\Component;

class ApplicationRoleStatusUpdate extends Component
{
    public Application $application;
    public Carbon $lastUpdated;

    /**
     * Mount the component
     * @param Application $application
     * @return void
     */
    public function mount(Application $application)
    {
        $this->application = $application;
        $this->lastUpdated = Carbon::now()->subSeconds(2);
    }

    /**
     * Update whether the applicant has been admitted as resident or not.
     * @param $workshop
     */
    public function switchResidentRole()
    {
        $this->authorize('finalize', Application::class);
        $this->application->update(['admitted_for_resident_status' => !$this->application->admitted_for_resident_status]);
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
        return view('auth.application.role_status_update_component');
    }
}
