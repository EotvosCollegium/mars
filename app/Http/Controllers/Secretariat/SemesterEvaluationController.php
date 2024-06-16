<?php

namespace App\Http\Controllers\Secretariat;

use App\Models\Faculty;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\PeriodicEvent;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Semester;
use App\Models\SemesterEvaluation;
use App\Models\SemesterStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Utils\PeriodicEventController;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SemesterEvaluationController extends PeriodicEventController
{
    public function __construct()
    {
        parent::__construct(PeriodicEvent::SEMESTER_EVALUATION_PERIOD);
    }

    /**
     * Update the PeriodicEvent for the evaluation form.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function updateEvaluationPeriod(Request $request): RedirectResponse
    {
        $this->authorize('manage', SemesterEvaluation::class);

        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:now|after:start_date'
        ]);

        $semester = Semester::find($request->semester_id);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $this->updatePeriodicEvent($semester, $startDate, $endDate);

        // setting start and end dates of questions
        $semester->questions()->update([
            'opened_at' => $startDate,
            'closed_at' => $endDate
        ]);

        return back()->with('message', __('general.successful_modification'));
    }


    /**
     * Show the evaluation form.
     * @throws AuthenticationException|AuthorizationException
     */
    public function show(): View
    {
        $this->authorize('fillOrManage', SemesterEvaluation::class);

        return view('secretariat.evaluation-form.app', [
            'phd' => user()->educationalInformation->studyLines()->currentlyEnrolled()->where('type', 'phd')->exists(),
            'user' => user(),
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all(),
            'evaluation' => user()->semesterEvaluations()->where('semester_id', self::semester()->id)->first(),
            'general_assemblies' => GeneralAssembly::all()->sortByDesc('closed_at')->take(2),
            'community_services' => user()->communityServiceRequests()->where('semester_id', self::semester()->id)->get(),
            'position_roles' => user()->roles()->whereIn('name', Role::STUDENT_POSTION_ROLES)->get(),
            'periodicEvent' => $this->periodicEvent(),
            'users_havent_filled_out' => user()->can('manage', SemesterEvaluation::class) ? User::doesntHaveStatusFor(self::semester()->succ())->get() : null,
        ]);
    }

    /**
     * Update form information.
     * @throws \Exception
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('fill', SemesterEvaluation::class);

        $validator = Validator::make($request->all(), [
            'section' => 'required|in:alfonso,courses,avg,general_assembly,anonymous_questions,other,status',
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
            'resign_residency' => 'nullable|in:on',
            'next_status' => ['nullable', Rule::in([SemesterStatus::ACTIVE, SemesterStatus::PASSIVE, Role::ALUMNI])],
            'next_status_note' => 'nullable|string|max:20',
            'will_write_request' => 'nullable|in:on',
        ]);
        $validator->validate();

        $user = user();
        $semester = $this->semester();
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
            case 'status':
                $evaluation->update(array_merge(
                    $request->only(['next_status', 'next_status_note']),
                    [
                        'will_write_request' => $request->has('will_write_request'),
                        'resign_residency' => $request->has('resign_residency')
                    ]
                ));
                if ($request->next_status == Role::ALUMNI) {
                    RoleUser::withoutEvents(function () use ($user) {
                        $user->removeRole(Role::collegist());
                        $user->addRole(Role::alumni());
                    });
                    return redirect()->route('home')->with('message', __('general.successful_modification'));
                } else {
                    if (!isset($request->next_status)) {
                        return back()->with('error', "A státusz megadása kötelező!")->with('section', $request->section);
                    }
                    $user->setStatusFor($this->semester()->succ(), $request->next_status, $request->next_status_note);
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
}
