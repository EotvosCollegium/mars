<?php

namespace App\Http\Livewire;

use App\Models\Semester;
use App\Models\User;
use Livewire\Component;

class EditStatus extends Component
{
    public Semester $semester;
    public User $user;
    public ?string $comment;
    public string $status;

    public function mount($user, $semester, $comment = '', $status = '')
    {
        $this->semester = $semester;
        $this->status = $status ?? '';
        $this->comment = $comment ?? '';
        $this->user = $user;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    public function save()
    {
        $this->user->setStatusFor($this->semester, $this->status, $this->comment);
        $this->emit('$refresh');
    }

    public function removeStatus()
    {
        $this->user->semesterStatuses()->detach($this->semester->id);
        $this->status = '';
        $this->comment = '';
    }

    public function render()
    {
        return view('user.edit_status_component');
    }
}
