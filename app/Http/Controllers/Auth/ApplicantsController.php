<?php

namespace App\Http\Controllers\Auth;

use App\Exports\ApplicantsExport;
use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\File;
use App\Models\Role;
use App\Utils\ApplicationHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller handling the admittance and administrative process.
 */
class ApplicantsController extends Controller
{
    use ApplicationHandler;

    /**
     * @param Request $request
     * @var User $authUser
     * @return View
     * @throws AuthorizationException
     */
    public function index(Request $request): View
    {
        $authUser = $request->user();
        $this->authorize('viewSomeApplication', User::class);

        $applications = ApplicationForm::select('*');
        $applications->join('workshop_users', 'application_forms.user_id', '=', 'workshop_users.user_id');
        if ($request->has('workshop') && $request->input('workshop') !== "null"){
            //filter by selected workshop
            if($authUser->cannot('viewAllApplications', User::class)) {
                $accessible_workshops = $authUser->roleWorkshops->concat($authUser->applicationCommitteWorkshops);
                if(!$accessible_workshops->contains($request->input('workshop'))) {
                    abort(403);
                }
            }
            $applications->where('workshop_id', $request->input('workshop'));
        } else {
            //filter by all accessible workshops
            if($authUser->cannot('viewAllApplications', User::class)) {
                $accessible_workshops = $authUser->roleWorkshops->concat($authUser->applicationCommitteWorkshops);
                $applications->whereIn('workshop_id', $accessible_workshops->pluck('id'));
            }
        }
        //hide unfinished
        if ($authUser->cannot('viewUnfinishedApplications', [User::class])) {
            $applications->where('status', '!=', ApplicationForm::STATUS_IN_PROGRESS);
        }
        //filter by status
        if ($request->has('status')) {
            $applications->where('status', $request->input('status'));
        }
        return view('auth.application.applications', [
            'applications' => $applications->with('user.educationalInformation')->get()->unique()->sortBy('user.name'),
            'workshop' => $request->input('workshop'), //filtered workshop
            'workshops' => $workshops, //workshops that can be chosen to filter
            'status' => $request->input('status'), //filtered status
            'applicationDeadline' => self::getApplicationDeadline(),
        ]);
    }
    public function show(Request $request, $id): View
    {
        $user = User::withoutGlobalScope('verified')->with('application')->findOrFail($id);
        $this->authorize('viewApplication', $user);

        return view('auth.application.applications_details', ['user' => $user,]);
    }

    /**
     * Edit an application's note.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function edit(Request $request, $id): RedirectResponse
    {
        $this->authorize('viewSomeApplication', User::class);
        $application = ApplicationForm::findOrFail($request->input('application'));
        $newStatus=$request->input('status_'.$application->user->id);
        if ($request->has('note')) {
            $application->update(['note' => $request->input('note')]);
        } elseif ($newStatus) {
            $application->update(['status' => $newStatus]);
        }
        return redirect()->back();
    }

    /**
     * Accept and delete applciations.
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function finalize()
    {
        $this->authorize('finalizeApplicationProcess', User::class);
        Cache::forget('collegists');
        $not_handled_applicants = User::query()->withoutGlobalScope('verified')
            ->where('verified', 0)
            ->whereHas('application', function ($query) {
                $query->whereIn('status', [ApplicationForm::STATUS_SUBMITTED, ApplicationForm::STATUS_SUBMITTED]);
            })
            ->count();
        if ($not_handled_applicants > 0) {
            return redirect()->back()->with('error', 'Még vannak feldolgozatlan jelentkezések!');
        }
        DB::transaction(function () {
            User::query()->withoutGlobalScope('verified')
                ->where('verified', 0)
                ->whereHas('application', function ($query) {
                    $query->where('status', ApplicationForm::STATUS_ACCEPTED);
                })
                ->update(['verified' => true]);
            $usersToDelete = User::query()->withoutGlobalScope('verified')
                ->where('verified', 0)->whereHas('application');
            foreach ($usersToDelete->get() as $user) {
                if ($user->profilePicture!=null) {
                    Storage::delete($user->profilePicture->path);
                    $user->profilePicture()->delete();
                }
            }
            $files = File::where('application_form_id', '!=', null);
            foreach ($files->get() as $file) {
                Storage::delete($file->path);
            }
            $files->delete();
            ApplicationForm::query()->delete();
            $usersToDelete->forceDelete();

            RoleUser::where('role_id', Role::get(Role::APPLICATION_COMMITTEE_MEMBER)->id)->delete();
            RoleUser::where('role_id', Role::get(Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER)->id)->delete();
        });

        Cache::clear();
        return back()->with('message', 'Sikeresen jóváhagyta az elfogadott jelentkezőket');
    }

    /**
     * Export all applications to excel
     */
    public function export()
    {
        $this->authorize('viewAllApplications', User::class);

        $applications = ApplicationForm::with('user')
                ->where('status', ApplicationForm::STATUS_SUBMITTED)
                ->orWhere('status', ApplicationForm::STATUS_CALLED_IN)
                ->orWhere('status', ApplicationForm::STATUS_ACCEPTED)
                ->get();

        return Excel::download(new ApplicantsExport($applications), 'felveteli.xlsx');

    }
}
