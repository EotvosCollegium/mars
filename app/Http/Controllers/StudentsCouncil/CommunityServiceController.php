<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Models\User;
use App\Models\Semester;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CommunityServiceController extends Controller
{
    public function index()
    {
        $this->authorize('view', \App\Models\CommunityService::class);
        


        return view('student-council.community-service.app', ['semesters' => $this->getCommunityServicesGroupedBySemesters(Auth::user())]);
    }

    private function getCommunityServicesGroupedBySemesters(User $user)
    {
        $this->authorize('view', \App\Models\CommunityService::class);

        return $user->activeSemesters()->orderBy('year', 'desc')
            ->orderBy('part', 'desc')
            ->get()
            ->where('tag', '<=', Semester::current()->tag)
            ->load([
                'communityServices' => function ($query) use ($user){
                    $query->where('requester_id', $user->id)->orWhere('approver_id', $user->id);
                },
            ]);
    }
}
