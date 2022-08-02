<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use App\Models\EducationalInformation;
use App\Models\Faculty;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    private const EDUCATIONAL_ROUTE = 'educational';
    private const QUESTIONS_ROUTE = 'questions';
    private const FILES_ROUTE = 'files';
    private const DELETE_FILE_ROUTE = 'files.delete';
    private const ADD_PROFILE_PIC_ROUTE = 'files.profile';
    private const SUBMIT_ROUTE = 'submit';
    private const PERSONAL_ROUTE = 'personal';

    /**
     * Return the view based on the request's page parameter.
     * @param Request $request
     * @return View
     */
    public function showApplicationForm(Request $request): View
    {
        if(!isset($request->user()->application)){
            $request->user()->application()->create();
        }

        $data = [
            'workshops' => Workshop::all(),
            'faculties' => Faculty::all(),
            'deadline' => self::getApplicationDeadline(),
            'deadline_extended' => self::isDeadlineExtended(),
            'countries' => require base_path('countries.php'),
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
    public function storeApplicationForm(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (isset($user->application) && $user->application->status == ApplicationForm::STATUS_SUBMITTED) {
            return redirect()->route('application')->with('error', 'Már véglegesítette a jelentkezését!');
        }

        switch ($request->input('page')) {
            case self::PERSONAL_ROUTE:
                $this->storePersonalData($request, $user);
                break;
            case self::EDUCATIONAL_ROUTE:
                $this->storeEducationalData($request, $user);
                break;
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
                $this->submitApplication($user);
                break;
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
    public function showApplications(Request $request)//: View
    {
        $authUser = $request->user();
        if ($request->has('id')) {
            // return one application in detail
            $user = User::withoutGlobalScope('verified')
                ->with('application')->findOrFail($request->input('id'));
            $this->authorize('viewApplication', $user);
            return view('auth.application.applications_details', [
                'user' => $user,
            ]);
        } else {
            //return all applications that can be visible
            $this->authorize('viewAnyApplication', User::class);
            if ($authUser->hasAnyRole([Role::NETWORK_ADMIN, Role::SECRETARY, Role::DIRECTOR])) {
                $workshops = Workshop::all();
                $applications = ApplicationForm::select('*');
                if ($request->has('workshop') && $request->input('workshop') !== "null") {
                    //filter by workshop
                    $applications->join('workshop_users', 'application_forms.user_id', '=', 'workshop_users.user_id')
                        ->where('workshop_id', $request->input('workshop'));
                }
                if ($request->has('status')) {
                    //filter by status
                    $applications->where('status', $request->input('status'));
                }
                session()->flash('can_filter_by_status');
            } else {
                $workshops = $authUser->roles()->whereIn('name', [Role::APPLICATION_COMMITTEE_MEMBER, Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR])->get(['object_id'])->pluck('object_id');
                $workshops = Workshop::whereIn('id', $workshops)->distinct()->get();
                $applications = ApplicationForm::where('status', ApplicationForm::STATUS_SUBMITTED);
                if ($request->has('workshop') && $request->input('workshop') !== "null") {
                    // filter by selected workshop
                    $applications->join('workshop_users', 'application_forms.user_id', '=', 'workshop_users.user_id')
                        ->where('workshop_id', $request->input('workshop'));
                } else {
                    // filter by user's workshops
                    $applications->join('workshop_users', 'application_forms.user_id', '=', 'workshop_users.user_id')
                        ->whereIn('workshop_id', $workshops->pluck('id'));
                }
            }

            return view('auth.application.applications', [
                'applications' => $applications->with('user.educationalInformation')->get()->unique(),
                'workshop' => $request->input('workshop'), //filtered workshop
                'workshops' => $workshops, //workshops that can be chosen to filter
                'status' => $request->input('status'), //filtered status
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
        if ($request->has('note')) {
            $application->update(['note' => $request->input('note')]);
        } elseif ($request->has('banish')) {
            $application->update(['status' => ApplicationForm::STATUS_BANISHED]);
        }
        return redirect()->back();
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
    public function storePersonalData(Request $request, User $user): void
    {
        $request->validate(RegisterController::PERSONAL_INFORMATION_RULES + ['name' => 'required|string|max:255']);
        $user->update(['name' => $request->input('name')]);
        $user->personalInformation()->update(
            $request->only([
            'place_of_birth',
            'date_of_birth',
            'mothers_name',
            'phone_number',
            'country',
            'county',
            'zip_code',
            'city',
            'street_and_number'])
        );
    }

    /**
     * @param Request $request
     * @param User $user
     * @return void
     */
    public function storeEducationalData(Request $request, User $user): void
    {
        $request->validate([
            'year_of_graduation' => 'required|integer|between:1895,' . date('Y'),
            'high_school' => 'required|string|max:255',
            'neptun' => 'required|string|size:6',
            'faculty' => 'required|array',
            'faculty.*' => 'exists:faculties,id',
            'educational_email' => 'required|string|email|max:255',
            'programs' => 'required|array',
            'programs.*' => 'nullable|string'
        ]);
        EducationalInformation::updateOrCreate(['user_id' => $user->id], [
            'year_of_graduation' => $request->input('year_of_graduation'),
            'high_school' => $request->input('high_school'),
            'neptun' => $request->input('neptun'),
            'year_of_acceptance' => date('Y'),
            'email' => $request->input('educational_email'),
            'program' => $request->input('programs'),
        ]);
        ApplicationForm::updateOrCreate(['user_id' => $user->id], [
            'graduation_average' => $request->input('graduation_average'),
            'semester_average' => $request->input('semester_average'),
            'language_exam' => $request->input('language_exam'),
            'competition' => $request->input('competition'),
            'publication' => $request->input('publication'),
            'foreign_studies' => $request->input('foreign_studies')
        ]);
        $user->faculties()->sync($request->input('faculty'));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return void
     */
    public function storeQuestionsData(Request $request, User $user): void
    {
        $request->validate([
            'status' => 'nullable|in:extern,resident',
            'workshop' => 'array',
            'workshop.*' => 'exists:workshops,id',
        ]);
        if ($request->input('status') == 'resident') {
            $user->setResident();
        } elseif ($request->input('status') == 'extern') {
            $user->setExtern();
        }
        $user->workshops()->sync($request->input('workshop'));
        ApplicationForm::updateOrCreate(['user_id' => $user->id], [
            'question_1' => $request->input('question_1'),
            'question_2' => $request->input('question_2'),
            'question_3' => $request->input('question_3'),
            'question_4' => $request->input('question_4'),
            'accommodation' => $request->input('accommodation')
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
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,svg|max:2048',
            'name' => 'required|string|max:255',
        ]);
        $path = $request->file('file')->store('uploads');
        $user->application()->firstOrCreate()->files()->create(['path' => $path, 'name' => $request->input('name')]);
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
     * @return void
     */
    public function submitApplication($user): void
    {
        if ($user->application->isReadyToSubmit()) {
            $user->application->update(['status' => ApplicationForm::STATUS_SUBMITTED]);
        } else {
            abort(400);
        }
    }
}
