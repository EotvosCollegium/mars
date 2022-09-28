<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Models\User;
use App\Models\Semester;
use App\Models\CommunityService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;



class CommunityServiceController extends Controller
{
    public function index()
    {
        $this->authorize('view', \App\Models\CommunityService::class);
        return view('student-council.community-service.app', ['semesters' => $this->getCommunityServicesGroupedBySemesters(Auth::user())]);
    }

    // Create a new community service
    public function create(Request $request)
    {
        $this->authorize('create', \App\Models\CommunityService::class);
        $request->validate([
            'approver' => 'required|exists:users,id',
            'description' => 'required|string',
        ]);

        $approver=User::find($request->approver);

        CommunityService::create([
            'requester_id' => Auth::user()->id,
            'approver_id' => $approver->id,
            'semester_id' => Semester::current()->id,
            'approved' => 0,
            'description' => $request->description,
        ]);
        
        return back()->with('message', __('community-service.created-scf'));
    }

    public function approve(CommunityService $communityService)
    {
        $this->authorize('approve', $communityService);
        
        $communityService->update(['approved' => 1]);

        return back()->with('message', __('community-service.approve_scf'));
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
