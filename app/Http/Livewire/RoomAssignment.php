<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Room;

class RoomAssignment extends Component
{
    public $rooms;
    public $unassignedUsers;
    public $usersInRoom;

    public function mount()
    {
        $this->rooms = Room::all();
        $residents = User::active()->resident()->get();
        $tenants = User::currentTenant()->get();
        $unassignedUsers = $residents->concat($tenants)->unique();
        $usersInRoom = [];
    }

    protected $rules = [
        'room.capacity' => 'numeric'
    ];

    public function increment_capacity($room)
    {
        $room->increment('capacity');
    }

    public function decrement_capacity($room)
    {
        $room->decrement('capacity');
    }


    public function render()
    {
        return view('dormitory.rooms.room_assignment_component');
    }
}
