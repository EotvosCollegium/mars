<?php

namespace App\Livewire;

use App\Models\Semester;
use App\Models\User;
use Livewire\Component;

class EditStatus extends Component
{
    public Semester $semester;
    public User $user;
    public ?string $comment;
    public string $status;

    /**
     * Gets the data from the @livewire parameters and sets the component properties.
     */
    public function mount($user, $semester, $comment = '', $status = '')
    {
        $this->semester = $semester;
        $this->status = $status ?? '';
        $this->comment = $comment ?? '';
        $this->user = $user;
    }

    /**
     * Sets the given status and saves it.
     */
    public function setStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    /**
     * Saves the current status with the current comment.
     */
    public function save()
    {
        $this->user->setStatusFor($this->semester, $this->status, $this->comment);
        $this->dispatch('$refresh');
    }

    /**
     * Deletes the status entry in the pivot table.
     */
    public function removeStatus()
    {
        $this->user->semesterStatuses()->detach($this->semester->id);
        $this->status = '';
        $this->comment = '';
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        return view('user.edit_status_component');
    }
}
