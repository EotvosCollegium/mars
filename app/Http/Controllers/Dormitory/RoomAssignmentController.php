<?php

namespace App\Http\Controllers\Dormitory;

use App\Http\Controllers\Controller;
use App\Models\Fault;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RoomAssignmentController extends Controller
{
    public function index()
    {
        return view('dormitory.rooms.app');
    }
}
