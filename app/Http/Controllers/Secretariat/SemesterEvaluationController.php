<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Mail\EvaluationFormClosed;
use App\Mail\StatusDeactivated;
use App\Models\Faculty;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Semester;
use App\Models\SemesterEvaluation;
use App\Models\SemesterStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Utils\HasPeriodicEvent;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SemesterEvaluationController extends Controller
{
    use HasPeriodicEvent;

    public function updateEvaluationPeriod(Request $request)
    {
        $this->authorize('manage', SemesterEvaluation::class);

        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:now',
        ]);

        $this->updatePeriodicEvent(
            $request->only(['semester_id', 'start_date', 'end_date'])
        );

        return back()->with('message', __('general.successful_modification'));

    }

    public function handlePeriodicEventStart(): void
    {
        self::sendEvaluationAvailableMail();
    }

    /**
     * Show the evaluation form.
     * @throws AuthenticationException|AuthorizationException
     */
    public function show()
    {
        $this->authorize('fillOrManage', SemesterEvaluation::class);

        return view('secretariat.evaluation-form.app', [
            'phd' => user()->educationalInformation->studyLines()->currentlyEnrolled()->where('type', 'phd')->exists(),
            'user' => user(),
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all(),
            'evaluation' => user()->semesterEvaluations()->where('semester_id', Semester::current()->id)->first(),
            'general_assemblies' => GeneralAssembly::all()->sortByDesc('closed_at')->take(2),
            'community_services' => user()->communityServiceRequests()->where('semester_id', Semester::current()->id)->get(),
            'position_roles' => user()->roles()->whereIn('name', Role::STUDENT_POSTION_ROLES)->get(),
            'periodicEvent' => $this->periodicEvent(),
        ]);
    }

    /**
     * Update form information.
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->authorize('fill', SemesterEvaluation::class);

        $validator = Validator::make($request->all(), [
            'section' => 'required|in:alfonso,courses,avg,general_assembly,feedback,other,status',
            'alfonso_note' => 'nullable|string',
            'courses' => 'nullable|array',
            'courses.name' => 'string',
            'courses.code' => 'string',
            'courses.grade' => 'numeric',
            'courses_note' => 'nullable|string',
            'current_avg' => 'nullable|numeric',
            'last_avg' => 'nullable|numeric',
            'general_assembly_note' => 'nullable|string',
            'professional_results' => 'nullable|array',
            'research' => 'nullable|array',
            'publications' => 'nullable|array',
            'conferences' => 'nullable|array',
            'scholarships' => 'nullable|array',
            'educational_activity' => 'nullable|array',
            'public_life_activities' => 'nullable|array',
            'can_be_shared' => 'nullable|in:on',
            'anonymous_feedback' => 'nullable|in:on',
            'feedback' => 'nullable|string',
            'resign_residency' => 'nullable|in:on',
            'next_status' => ['nullable', Rule::in([SemesterStatus::ACTIVE, SemesterStatus::PASSIVE, Role::ALUMNI])],
            'next_status_note' => 'nullable|string|max:20',
            'will_write_request' => 'nullable|in:on',
        ]);
        $validator->validate();

        $user = user();
        $semester = self::semester();
        if(!$semester) {
            throw new \Exception('No semester found for the event');
        }
        $evaluation = $user->semesterEvaluations()->where('semester_id', $semester->id)->first();
        if (!$evaluation) {
            $evaluation = SemesterEvaluation::create(['semester_id' => $semester->id, 'user_id' => $user->id]);
        }
        switch ($request->section) {
            case 'alfonso':
                $evaluation->update($request->only('alfonso_note'));
                break;
            case 'courses':
                $evaluation->update($request->only(['courses', 'courses_note']));
                break;
            case 'avg':
                $evaluation->update($request->only(['current_avg', 'last_avg']));
                break;
            case 'general_assembly':
                $evaluation->update($request->only('general_assembly_note'));
                break;
            case 'other':
                $evaluation->update(array_merge(
                    $request->only(['professional_results', 'research', 'publications', 'conferences', 'scholarships', 'educational_activity', 'public_life_activities']),
                    ['can_be_shared' => $request->has('can_be_shared')]
                ));
                break;
            case 'feedback':
                if ($request->has('anonymous_feedback')) {
                    Mail::to(User::president())
                        ->queue(new \App\Mail\AnonymousFeedback(User::president()->name, $request->feedback));
                    Mail::to(User::studentCouncilSecretary())
                        ->queue(new \App\Mail\AnonymousFeedback(User::studentCouncilSecretary()->name, $request->feedback));
                } else {
                    $evaluation->update($request->only(['feedback']));
                }
                break;
            case 'status':
                $evaluation->update(array_merge(
                    $request->only(['next_status', 'next_status_note']),
                    [
                        'will_write_request' => $request->has('will_write_request'),
                        'resign_residency' => $request->has('resign_residency')
                    ]
                ));
                if ($request->next_status == Role::ALUMNI) {
                    self::deactivateCollegist($user);
                    return redirect()->route('home')->with('message', __('general.successful_modification'));
                } else {
                    if (!isset($request->next_status)) {
                        return back()->with('error', "A státusz megadása kötelező!")->with('section', $request->section);
                    }
                    $user->setStatusFor(self::semester()->succ(), $request->next_status, $request->next_status_note);
                    if ($request->has('resign_residency') && $user->isResident()) {
                        $user->setExtern();
                    }
                }
                break;
            default:
                throw new \Exception('Invalid section: ' . $request->section);
        }

        return back()->with('message', __('general.successful_modification'))->with('section', $request->section);
    }

    /**
     * Send out the request to fill out the form.
     */
    public static function sendEvaluationAvailableMail(): void
    {
        Mail::to(config('contacts.mail_membra'))->queue(new \App\Mail\EvaluationFormAvailable());
        if (User::secretary()) {
            Mail::to(User::secretary())->queue(new \App\Mail\EvaluationFormAvailableDetails(User::secretary()->name));
        }
        if (User::president()) {
            Mail::to(User::president())->queue(new \App\Mail\EvaluationFormAvailableDetails(User::president()->name));
        }
    }

    /**
     * Send out a reminder.
     */
    public static function sendEvaluationReminder(): void
    {
        //TODO
        $userCount = self::usersHaventFilledOutTheForm()->count();

        Mail::to(config('contacts.mail_membra'))->queue(new \App\Mail\EvaluationFormReminder($userCount));
    }

    /**
     * @return User[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public static function usersHaventFilledOutTheForm()
    {
        return User::withRole(Role::COLLEGIST)->verified()->whereDoesntHave('semesterStatuses', function ($query) {
            $query->where('semester_id', Semester::next()->id);
        })->get();
    }

    /**
     * Those who did not make their statements by now will be deactivated
     * next semester.
     */
    public static function finalizeStatements()
    {
        $users = self::usersHaventFilledOutTheForm();
        $users_names = $users->pluck('name')->toArray();

        if (User::secretary()) {
            Mail::to(User::secretary())->queue(new EvaluationFormClosed(User::secretary()->name, $users_names));
        }
        if (User::president()) {
            Mail::to(User::president())->queue(new EvaluationFormClosed(User::president()->name, $users_names));
        }
        if (User::director()) {
            Mail::to(User::director())->queue(new EvaluationFormClosed(User::director()->name, $users_names));
        }
        foreach (User::workshopLeaders() as $user) {
            Mail::to($user)->queue(new EvaluationFormClosed($user->name));
        }

        foreach ($users as $user) {
            Mail::to($user)->queue(new StatusDeactivated($user->name));
            RoleUser::withoutEvents(function () use ($user) {
                self::deactivateCollegist($user);
            });

        }
    }

    /**
     * Deactivate a collegist and set alumni role.
     * @param User $user
     */
    public static function deactivateCollegist(User $user)
    {
        $user->removeRole(Role::collegist());
        $user->addRole(Role::alumni());
    }
}
