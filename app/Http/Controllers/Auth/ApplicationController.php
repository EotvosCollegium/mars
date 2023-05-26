<?php

namespace App\Http\Controllers\Auth;

use App\Exports\ApplicantsExport;
use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use App\Models\Faculty;
use App\Models\User;
use App\Models\Workshop;
use App\Models\RoleUser;
use App\Models\File;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ApplicationController extends Controller
{
    private const EDUCATIONAL_ROUTE = 'educational';
    private const QUESTIONS_ROUTE = 'questions';
    private const FILES_ROUTE = 'files';
    private const DELETE_FILE_ROUTE = 'files.delete';
    private const ADD_PROFILE_PIC_ROUTE = 'files.profile';
    private const SUBMIT_ROUTE = 'submit';

    /**
     * Return the view based on the request's page parameter.
     * @param Request $request
     * @return View
     */
    public function showApplicationForm(Request $request): View
    {
        if (!isset($request->user()->application)) {
            $request->user()->application()->create();
        }

        $data = [
            'workshops' => Workshop::all(),
            'faculties' => Faculty::all(),
            'deadline' => self::getApplicationDeadline(),
            'deadline_extended' => self::isDeadlineExtended(),
            'user' => $request->user()
        ];
        switch ($request->input('page')) {
            case (self::EDUCATIONAL_ROUTE):
                return view('auth.application.educational', $data);
            case (self::QUESTIONS_ROUTE):
                return view('auth.application.questions', $data);
            case (self::FILES_ROUTE):
                return view('auth.application.files', $data);
            case (self::SUBMIT_ROUTE):
                return view('auth.application.submit', $data);
            default:
                return view('auth.application.personal', $data);
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeApplicationForm(Request $request)
    {
        $user = $request->user();

        if (now() > self::getApplicationDeadline()) {
            return redirect()->route('application')->with('error', 'A jelentkezési határidő lejárt');
        }

        if (isset($user->application) && $user->application->status == ApplicationForm::STATUS_SUBMITTED) {
            return redirect()->route('application')->with('error', 'Már véglegesítette a jelentkezését!');
        }

        switch ($request->input('page')) {
            //personal and educational data update is in UserController
            case self::QUESTIONS_ROUTE:
                $this->storeQuestionsData($request, $user);
                break;
            case self::FILES_ROUTE:
                $this->storeFiles($request, $user);
                break;
            case self::DELETE_FILE_ROUTE:
                $this->deleteFile($request, $user);
                break;
            case self::ADD_PROFILE_PIC_ROUTE:
                $this->storeProfilePicture($request, $user);
                break;
            case self::SUBMIT_ROUTE:
                return $this->submitApplication($user);
            default:
                abort(404);
        }
        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /**
     * @param Request $request
     * @var User $authUser
     * @return View
     * @throws AuthorizationException
     */
    public function showApplications(Request $request): View
    {
        $authUser = $request->user();
        if ($request->has('id')) { // return one application in detail
            $user = User::withoutGlobalScope('verified')
                ->with('application')->findOrFail($request->input('id'));
            $this->authorize('viewApplication', $user);
            return view('auth.application.applications_details', [
                'user' => $user,
            ]);
        } else { //return all applications that can be visible
            $this->authorize('viewAnyApplication', User::class);
            $workshops = $authUser->applicationWorkshops();
            $applications = ApplicationForm::select('*');
            $applications->join('workshop_users', 'application_forms.user_id', '=', 'workshop_users.user_id');
            if ($request->has('workshop') && $request->input('workshop') !== "null" && $workshops->contains($request->input('workshop'))) {
                //filter by workshop selected
                $applications->where('workshop_id', $request->input('workshop'));
            } else {
                //filter by accessible workshops
                $applications->whereIn('workshop_id', $workshops->pluck('id'));
            }
            //hide unfinished
            if ($authUser->cannot('viewUnfinishedApplications', [User::class])) {
                $applications->where(function ($query) {
                    $query->where('status', ApplicationForm::STATUS_SUBMITTED)
                        ->orWhere('status', ApplicationForm::STATUS_CALLED_IN)
                        ->orWhere('status', ApplicationForm::STATUS_ACCEPTED);
                });
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
    }

    /**
     * Edit an application's note.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function editApplication(Request $request): RedirectResponse
    {
        $this->authorize('viewAnyApplication', User::class);
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
    public function finalizeApplicationProcess()
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

        Cache::forget('collegists');
        return back()->with('message', 'Sikeresen jóváhagyta az elfogadott jelentkezőket');
    }

    /**
     * @return Carbon the application deadline set in .env
     */
    public static function getApplicationDeadline(): Carbon
    {
        return Carbon::parse(config('custom.application_deadline'));
    }

    /**
     * @return bool if the deadline has been extended or not
     */
    public static function isDeadlineExtended(): bool
    {
        return config('custom.application_extended');
    }


    /**
     * @param Request $request
     * @param User $user
     * @return void
     */
    public function storeQuestionsData(Request $request, User $user): void
    {
        $request->validate([
            'status' => 'required|in:extern,resident',
            'graduation_average' => 'required|numeric',
        ]);
        if ($request->input('status') == 'resident') {
            $user->setResident();
        } elseif ($request->input('status') == 'extern') {
            $user->setExtern();
        }

        ApplicationForm::updateOrCreate(['user_id' => $user->id], [
            'graduation_average' => $request->input('graduation_average'),
            'semester_average' => $request->input('semester_average'),
            'competition' => $request->input('competition'),
            'publication' => $request->input('publication'),
            'foreign_studies' => $request->input('foreign_studies'),
            'question_1' => $request->input('question_1'),
            'question_2' => $request->input('question_2'),
            'question_3' => $request->input('question_3'),
            'question_4' => $request->input('question_4'),
            'accommodation' => $request->input('accommodation') === "on",
            'present' => $request->input('present')
        ]);
    }

    /**
     * @param Request $request
     * @param $user
     * @return void
     */
    public function storeFiles(Request $request, $user): void
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5240',
            'name' => 'required|string|max:255',
        ]);
        $path = $request->file('file')->store('uploads');
        $user->application->files()->create(['path' => $path, 'name' => $request->input('name')]);
    }

    /**
     * @param Request $request
     * @param $user
     * @return void
     */
    public function deleteFile(Request $request, $user): void
    {
        $request->validate([
            'id' => 'required|exists:files',
        ]);

        $file = $user->application->files()->findOrFail($request->input('id'));

        Storage::delete($file->path);
        $file->delete();
    }

    /**
     * @param Request $request
     * @param $user
     * @return void
     */
    public function storeProfilePicture(Request $request, $user): void
    {
        $request->validate([
            'picture' => 'required|mimes:jpg,jpeg,png,gif,svg',
        ]);
        $path = $request->file('picture')->store('avatars');
        $old_profile = $user->profilePicture;
        if ($old_profile) {
            Storage::delete($old_profile->path);
            $old_profile->update(['path' => $path]);
        } else {
            $user->profilePicture()->create(['path' => $path, 'name' => 'profile_picture']);
        }
    }

    /**
     * @param $user
     * @return RedirectResponse
     */
    public function submitApplication(User $user)
    {
        $user->load('application');
        if ($user->application->missingData() == []) {
            $user->application->update(['status' => ApplicationForm::STATUS_SUBMITTED]);
            $user->internetAccess->setWifiCredentials($user->educationalInformation->neptun);
            $user->internetAccess()->update(['has_internet_until' => $this::getApplicationDeadline()->addMonth(1)]);
            return back()->with('message', 'Sikeresen véglegesítette a jelentkezését!');
        } else {
            return back()->with('error', 'Hiányzó adatok!');
        }
    }

    public function exportApplications()
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
