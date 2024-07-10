<?php

namespace App\Http\Controllers\Auth;

use App\Exports\ApplicantsExport;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Semester;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\File;
use App\Models\Role;
use App\Models\Workshop;
use App\Utils\ApplicationHandler;
use App\Utils\HasPeriodicEvent;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
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
class AdmissionController extends Controller
{
    use ApplicationHandler;
    use HasPeriodicEvent;

    public function __construct()
    {
        $this->underlyingControllerName =
            \App\Http\Controllers\Auth\ApplicationController::class;
    }

    /**
     * Update the PeriodicEvent connected to the applications.
     * @throws AuthorizationException
     */
    public function updateApplicationPeriod(Request $request): RedirectResponse
    {
        $this->authorize('finalize', Application::class);

        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:now|after:start_date',
            'extended_end_date' => 'nullable|date|after:end_date',
        ]);

        $this->updatePeriodicEvent(
            Semester::find($request->semester_id),
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date),
            $request->extended_end_date ? Carbon::parse($request->extended_end_date) : null
        );

        return back()->with('message', __('general.successful_modification'));
    }

    /**
     * @param Request $request
     * @var User $authUser
     * @return View
     * @throws AuthorizationException
     */
    public function index(Request $request): View
    {
        $authUser = $request->user();
        $this->authorize('viewSome', Application::class);

        $applications = Application::query();
        $filtered_workshop = $this->getFilteredWorkshop($request);
        $accessible_workshops = $this->getAccessibleWorkshops($authUser);
        $show_not_submitted = (bool)$request->input('show_not_submitted');

        $applications->where(function ($query) use ($accessible_workshops, $filtered_workshop) {
            $query->whereHas('applicationWorkshops', function ($query) use ($accessible_workshops, $filtered_workshop) {
                $query->whereIn('workshop_id', $accessible_workshops->pluck('id'));
                if ($filtered_workshop) {
                    $query->where('workshop_id', $filtered_workshop->id);
                }
            })->orWhereDoesntHave('applicationWorkshops');
        });

        //hide unfinished
        if ($authUser->cannot('viewUnfinished', Application::class)) {
            $applications->where('submitted', true);
        } else {
            $applications->where('submitted', !$show_not_submitted);
        }
        //filter by status
        //        if ($request->has('status')) {
        //            $applications->where('status', $request->input('status'));
        //        }

        $applications = $applications->get()->unique()->sortBy('user.name');
        session()->push('applications', implode(":", $applications->pluck('user.name')->toArray()));
        return view('auth.admission.index', [
            'applications' => $applications,
            'workshop' => $request->input('workshop'), //filtered workshop
            'workshops' => $accessible_workshops, //workshops that can be chosen to filter
            'show_not_submitted' => $show_not_submitted,
            'applicationDeadline' => $this->getDeadline(),
            'periodicEvent' => $this->periodicEvent()
        ]);
    }

    /**
     * Show and application's details
     *
     * @param Application $application
     * @return View
     * @throws AuthorizationException
     */
    public function show(Application $application): View
    {
        $this->authorize('view', $application);
        $user = User::withoutGlobalScope('verified')->with('application')->find($application->user_id);
        return view('auth.admission.application', ['user' => $user]);
    }

    /**
     * Edit an application's note.
     * @param Request $request
     * @param Application $application
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);
        $newStatus = $request->input('status_'.$application->user->id);
        if ($request->has('note')) {
            $application->update(['note' => $request->input('note')]);
        } elseif ($newStatus) {
            $this->authorize('editStatus', $application);
            $application->update(['status' => $newStatus]);
        }
        return redirect()->back();
    }

    /**
     * Accept and delete applciations.
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function finalize(): RedirectResponse
    {
        //        $this->authorize('finalizeApplicationProcess', User::class);
        //        Cache::forget('collegists');
        //        $not_handled_applicants = User::query()->withoutGlobalScope('verified')
        //            ->where('verified', 0)
        //            ->whereHas('application', function ($query) {
        //                $query->where('submitted', true);
        //            })
        //            ->count();
        //        if ($not_handled_applicants > 0) {
        //            return redirect()->back()->with('error', 'Még vannak feldolgozatlan jelentkezések!');
        //        }
        //        DB::transaction(function () {
        //            User::query()->withoutGlobalScope('verified')
        //                ->where('verified', 0)
        //                ->whereHas('application', function ($query) {
        //                    $query->where('status', Application::STATUS_ACCEPTED);
        //                })
        //                ->update(['verified' => true]);
        //            $usersToDelete = User::query()->withoutGlobalScope('verified')
        //                ->where('verified', 0)->whereHas('application');
        //            foreach ($usersToDelete->get() as $user) {
        //                if ($user->profilePicture!=null) {
        //                    Storage::delete($user->profilePicture->path);
        //                    $user->profilePicture()->delete();
        //                }
        //            }
        //            $files = File::where('application_id', '!=', null);
        //            foreach ($files->get() as $file) {
        //                Storage::delete($file->path);
        //            }
        //            $files->delete();
        //            Application::query()->delete();
        //            $usersToDelete->forceDelete();
        //
        //            RoleUser::where('role_id', Role::get(Role::APPLICATION_COMMITTEE_MEMBER)->id)->delete();
        //            RoleUser::where('role_id', Role::get(Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER)->id)->delete();
        //        });
        //
        //        Cache::clear();
        return back()->with('message', 'Sikeresen jóváhagyta az elfogadott jelentkezőket');
    }

    /**
     * Export all applications to excel
     */
    public function export()
    {
        $this->authorize('viewAll', Application::class);

        $applications = Application::with('user')
                ->where('submitted', true)
                ->get();

        return Excel::download(new ApplicantsExport($applications), 'felveteli.xlsx');

    }

    /**
     * @param Request $request
     * @return Workshop|null
     */
    private function getFilteredWorkshop(Request $request): mixed
    {
        if ($request->has('workshop') && $request->input('workshop') !== "null") {
            return Workshop::find($request->get('workshop'));
        }
        return null;
    }

    /**
     * @param User $user
     * @return Collection|Workshop[]
     */
    public function getAccessibleWorkshops(User $user): Collection
    {
        if ($user->cannot('viewAll', Application::class)) {
            return $user->roleWorkshops->concat($user->applicationCommitteWorkshops);
        }
        return Workshop::all();
    }
}
