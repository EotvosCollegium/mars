<?php

namespace App\Http\Controllers\Dormitory;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Room;
use App\Models\User;

class RoomController extends Controller
{
    public function index(){
        $this->authorize('viewAny', Room::class);
        $users=User::all();
        $rooms = Room::with('users')->get();
        return view('dormitory.rooms.app', ['users' => $users, 'rooms' => $rooms]);
    }

    public function updateRoomCapacity(Room $room, Request $request)
    {
        $this->authorize('updateAny', Room::class);

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:add,remove'
        ]);
        $validator->validate();
        $new_capacity=$room->capacity+($request->type=='add'?1:-1);
        if($new_capacity<$room->residentNumber()){
            return back()->with('error', 'hiba');
        }
        if($new_capacity>4 || $new_capacity<0){
            return back()->with('error', 'masikhiba');
        }
        if($request->type=='add'){
            $room->increment('capacity');
            return back();
        }else{
            $room->decrement('capacity');
        }

        return back();
    }

    public function updateResidents(Request $request){
        // return $request;
        $rooms=Room::all();
        foreach ($rooms as $room) {
            $userIds=$request->get($room->name);
            if($userIds!=null){
                foreach($userIds as $userId){
                    $user=User::find($userId);
                    $user->update(['room' => $room->name]);
                }
            }
            
        }
        return back();
    }
}
