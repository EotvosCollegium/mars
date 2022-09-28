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
use Illuminate\Support\Facades\Mail;


class CommunityServiceController extends Controller
{
    public function index()
    {
        $this->authorize('view', \App\Models\CommunityService::class);
        return view('student-council.community-service.app', ['semesters' => $this->getCommunityServicesGroupedBySemesters(Auth::user())]);
    }

    public function search(Request $request)
    {
        $this->authorize('approveAny', \App\Models\CommunityService::class);
        if ($request->requester == null) {
            return view('student-council.community-service.search');
        }
        $request->validate([
            'requester' => 'required|exists:users,id',
        ]);

        $requester=User::find($request->requester);
        return view('student-council.community-service.search', ['semesters' => $this->getCommunityServicesGroupedBySemesters($requester, false)]);
    }

    // Create a new community service
    public function create(Request $request)
    {
        if($request->user()->cannot('create', \App\Models\CommunityService::class)){
            return back()->with('message', __('community-service.created-not-allowed'));
        }
        $request->validate([
            'approver' => 'required|exists:users,id',
            'description' => 'required|string',
        ]);

        $approver=User::find($request->approver);

        $communityService=CommunityService::create([
            'requester_id' => Auth::user()->id,
            'approver_id' => $approver->id,
            'semester_id' => Semester::current()->id,
            'approved' => 0,
            'description' => $request->description,
        ]);

        Mail::to($communityService->approver)->queue(new \App\Mail\CommunityServiceRequested($communityService));

        return back()->with('message', __('community-service.created-scf'));
    }

    public function approve(CommunityService $communityService)
    {
        $this->authorize('approve', $communityService);
        
        $communityService->update(['approved' => 1]);

        Mail::to($communityService->requester)->queue(new \App\Mail\CommunityServiceApproved($communityService));

        return back()->with('message', __('community-service.approve_scf'));
    }


    private function getCommunityServicesGroupedBySemesters(User $user, bool $showWhereApprover = true)
    {
        $this->authorize('view', \App\Models\CommunityService::class);

        return $showWhereApprover?
            $user->activeSemesters()->orderBy('year', 'desc')
                ->orderBy('part', 'desc')
                ->get()
                ->where('tag', '<=', Semester::current()->tag)
                ->load([
                    'communityServices' => function ($query) use ($user){
                        $query->where('requester_id', $user->id)->orWhere('approver_id', $user->id);
                    },
                ])
            :
            $user->activeSemesters()->orderBy('year', 'desc')
            ->orderBy('part', 'desc')
            ->get()
            ->where('tag', '<=', Semester::current()->tag)
            ->load([
                'communityServices' => function ($query) use ($user){
                    $query->where('requester_id', $user->id);
                },
            ]);
    }
}
