<?php

namespace App\Http\Livewire;

use App\Models\GeneralAssemblies\GeneralAssembly;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ExcusedUsers extends Component
{
    public GeneralAssembly $general_assembly;
    public $user;
    public $comment;

    public function addUser()
    {
        $this->validate([
            'user' => 'required|exists:users,id',
            'comment' => 'nullable|string|max:255',
        ]);
        $this->general_assembly->excusedUsers()->attach($this->user, ['comment' => $this->comment]);
        $this->user = null;
        $this->comment = null;
    }

    public function removeUser($userId)
    {
        $this->general_assembly->excusedUsers()->detach($userId);
    }

    public function render()
    {
        return view('livewire.excused-users');
    }
}
