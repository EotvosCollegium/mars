<?php

namespace App\Livewire;

use App\Models\ApplicationForm;
use Livewire\Component;

class ApplicationStatusUpdate extends Component
{
    public ApplicationForm $applicationForm;

    /**
     * Gets the data from the @livewire parameters and sets the component properties.
     */
    public function mount($applicationForm)
    {
        $this->applicationForm = $applicationForm;
    }

    /**
     * Updates the status of the application and flashes a confirmation message.
     */
    public function setStatus($status)
    {
        $this->applicationForm->update(['status' => $status]);
        session()->flash('message', 'Státusz frissítve!');
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        return view('auth.application.status_update_component');
    }
}
