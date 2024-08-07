<?php

namespace App\Http\Controllers\Auth;

use App\Exports\ApplicantsExport;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationWorkshop;
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
     * @return View
     * @throws AuthorizationException
     */
    public function index(Request $request): View
    {
        $request->validate([
            'status_filter' => 'in:everybody,unsubmitted,submitted,called_in,admitted',
        ]);
        $authUser = $request->user();
        $this->authorize('viewSome', Application::class);

        $applications = Application::query();
        $accessible_workshops = $this->getAccessibleWorkshops($authUser);
        $filtered_workshop = $this->getFilteredWorkshop($request);
        $status_filter = $request->input('status_filter');
        if(!isset($status_filter)) {
            $status_filter = 'submitted';
        }

        $should_show_unsubmitted = $status_filter == 'everybody' || $status_filter == 'unsubmitted';
        if ($authUser->cannot('viewUnfinished', Application::class) && $should_show_unsubmitted) {
            abort(403, 'You are not authorized to access unsubmitted applications.');
        }

        $applications->where(function ($query) use ($accessible_workshops, $filtered_workshop, $status_filter, $should_show_unsubmitted) {
            $query->whereHas('applicationWorkshops', function ($query) use ($accessible_workshops, $filtered_workshop, $status_filter) {
                $query->whereIn('workshop_id', $accessible_workshops->pluck('id'));
                if ($filtered_workshop) {
                    $query->where('workshop_id', $filtered_workshop->id);
                }

                if ($status_filter == 'admitted') {
                    $query->where('admitted', true);
                } elseif ($status_filter == 'called_in') {
                    $query->where(function ($query) {
                        $query->where('called_in', true)
                              ->orWhere('admitted', true);
                    });
                }
            });
            if (!$filtered_workshop && $should_show_unsubmitted) {
                $query->orWhereDoesntHave('applicationWorkshops');
            }
        });

        if ($status_filter == 'unsubmitted') {
            $applications->where('submitted', false);
        } elseif ($status_filter == 'submitted') {
            $applications->where('submitted', true);
        }

        $applications = $applications->with('user.educationalInformation')->distinct()->get()->sortBy('user.name');
        return view('auth.admission.index', [
            'applications' => $applications,
            'workshop' => $request->input('workshop'), //filtered workshop
            'workshops' => $accessible_workshops, //workshops that can be chosen to filter
            'status_filter' => $status_filter,
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
    public function updateNote(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);
        if (user()->id == $application->user_id) {
            return redirect()->back()->with('error', 'You cannot modify the internal note of yourself.');
        }

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
        $this->authorize('finalize', Application::class);
        Cache::forget('collegists');
        DB::transaction(function () {
            $admitted_applications = Application::query()
                ->whereHas('applicationWorkshops', function ($query) {
                    $query->where('admitted', true);
                })->get();
            $not_admitted_applications = Application::whereNotIn('id', $admitted_applications->pluck('id'))->get();
            // admit users
            foreach ($admitted_applications as $application) {
                $application->user->update(['verified' => true]);
                if($application->admitted_for_resident_status) {
                    $application->user->setResident();
                } else {
                    $application->user->setExtern();
                }
                $application->user->workshops()->sync($application->applicationWorkshops()->where('admitted', true)->pluck('workshop_id'));
            }
            // delete data for not admitted users
            $files = File::query()
                ->whereIn('application_id', $not_admitted_applications->pluck('id')) // application files
                ->orWhereIn('user_id', $not_admitted_applications->pluck('user_id')); // profile pictures
            foreach ($files->get() as $file) {
                Storage::delete($file->path);
            }
            $files->delete();
            // soft deletes application, keep them for future reference
            // (see https://github.com/EotvosCollegium/mars/issues/332#issuecomment-2014058021)
            Application::whereIn('id', $admitted_applications->pluck('id'))->delete();
            Application::whereNotIn('id', $admitted_applications->pluck('id'))->forceDelete();
            ApplicationWorkshop::query()->delete();

            User::query()->withoutGlobalScope('verified')->whereIn('id', $not_admitted_applications->pluck('user_id'))->forceDelete();

            RoleUser::where('role_id', Role::get(Role::APPLICATION_COMMITTEE_MEMBER)->id)->delete();
            RoleUser::where('role_id', Role::get(Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER)->id)->delete();
        });

        Cache::clear();
        return back()->with('message', 'Sikeresen jóváhagyta az elfogadott jelentkezőket és törölte a fel nem vett jelentkezőket.');
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
