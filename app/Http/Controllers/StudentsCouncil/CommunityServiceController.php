<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Models\User;
use App\Models\Semester;
use App\Models\CommunityService;

use App\Http\Controllers\Controller;
use App\Mail\CommunityServiceStatusChanged;
use App\Mail\CommunityServiceRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CommunityServiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', CommunityService::class);

        return view('student-council.community-service.app', [
            'semesters' => Semester::whereHas('communityServices', function ($query) use ($request) {
                $query->where('approver_id', $request->user()->id)
                                    ->orWhere('requester_id', $request->user()->id);
            })
                            ->with('communityServices')
                            ->get(),
            'possible_approvers' => User::studentCouncilLeaders()
        ]);
    }

    public function search(Request $request)
    {
        $this->authorize('approveAny', CommunityService::class);

        $request->validate([
            'requester' => 'nullable|exists:users,id',
        ]);

        return view('student-council.community-service.search', [
            'semesters' => Semester::whereRelation('communityServices', 'requester_id', $request->requester)
                            ->with('communityServices')
                            ->get(),
            'selectedUser' => User::find($request->requester),
        ]);
    }

    // Create a new community service
    public function create(Request $request)
    {
        if ($request->user()->cannot('create', CommunityService::class)) {
            return back()->with('message', __('community-service.created-not-allowed'));
        }
        $request->validate([
            'approver' => 'required|exists:users,id',
            'description' => 'required|string',
        ]);

        $communityService=CommunityService::create([
            'requester_id' => Auth::user()->id,
            'approver_id' => $request->approver,
            'semester_id' => Semester::current()->id,
            'approved' => null,
            'description' => $request->description,
        ]);

        Mail::to($communityService->approver)->queue(new CommunityServiceRequested($communityService));

        return back()->with('message', __('community-service.created-scf'));
    }

    public function approve(CommunityService $communityService)
    {
        $this->authorize('approve', $communityService);

        $communityService->update(['approved' => 1]);

        Mail::to($communityService->requester)->queue(new CommunityServiceStatusChanged($communityService));

        return back()->with('message', __('community-service.approve_scf'));
    }

    public function reject(CommunityService $communityService)
    {
        $this->authorize('approve', $communityService);

        $communityService->update(['approved' => 0]);

        Mail::to($communityService->requester)->queue(new CommunityServiceStatusChanged($communityService));

        return back()->with('message', __('community-service.reject_scf'));
    }
}
