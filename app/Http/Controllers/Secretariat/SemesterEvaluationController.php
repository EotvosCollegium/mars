<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\EventTrigger;
use App\Models\Faculty;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\Role;
use App\Models\Semester;
use App\Models\SemesterEvaluation;
use App\Models\SemesterStatus;
use App\Models\User;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SemesterEvaluationController extends Controller
{
    /**
     * Check if the evaluation is available.
     */
    public static function isEvaluationAvailable()
    {
        $available = EventTrigger::find(EventTrigger::SEMESTER_EVALUATION_AVAILABLE)->date;
        $deadline = self::deadline();

        return now() <= $deadline && $available >= Semester::next()->getStartDate();
    }

    public static function deadline(): Carbon
    {
        $custom_deadline = config('custom.semester_evaluation_deadline');
        $system_deadline = EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->date;
        if(!isset($custom_deadline)) {
            return $system_deadline;
        } else {
            $custom_deadline = Carbon::parse($custom_deadline);
            //if the deadline has not been updated, use the system_deadline
            if($custom_deadline < Semester::current()->getStartDate()) {
                return $system_deadline;
            } else {
                return $custom_deadline;
            }
        }
    }

    /**
     * Show the evaluation form.
     */
    public function show()
    {
        $this->authorize('is-collegist');
        if (!self::isEvaluationAvailable()) {
            return redirect('home')->with('error', 'Lejárt a határidő a kérdőív kitöltésére. Keresd fel a titkárságot.');
        }
        return view('secretariat.evaluation-form.app', [
            'user' => user(),
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all(),
            'evaluation' => user()->semesterEvaluations()->where('semester_id', Semester::current()->id)->first(),
            'general_assemblies' => GeneralAssembly::all()->sortByDesc('closed_at')->take(2),
            'community_services' => user()->communityServiceRequests()->where('semester_id', Semester::current()->id)->get(),
            'position_roles' => user()->roles()->whereIn('name', Role::STUDENT_POSTION_ROLES)->get(),
            'deadline' => self::deadline(),
        ]);
    }

    /**
     * Update form information.
     */
    public function store(Request $request)
    {
        $this->authorize('is-collegist');
        if (!self::isEvaluationAvailable()) {
            return redirect('home')->with('error', 'Lejárt a határidő a kérdőív kitöltésére. Keresd fel a titkárságot.');
        }

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
        $evaluation = $user->semesterEvaluations()->where('semester_id', Semester::current()->id)->first();
        if (!$evaluation) {
            $evaluation = SemesterEvaluation::create(['semester_id' => Semester::current()->id, 'user_id' => $user->id]);
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
                if($request->has('anonymous_feedback')) {
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
                } else {
                    if(!isset($request->next_status)) {
                        return back()->with('error', "A státusz megadása kötelező!")->with('section', $request->section);
                    }
                    $user->setStatusFor(Semester::next(), $request->next_status, $request->next_status_note);
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
    public static function sendEvaluationAvailableMail()
    {
        Mail::to(env('MAIL_MEMBRA'))->queue(new \App\Mail\EvaluationFormAvailable());
    }

    /**
     * Those who did not make their statements by now will be deactivated
     * next semester.
     */
    public static function finalizeStatements()
    {
        foreach (User::collegists() as $user) {
            if (! $user->getStatus(Semester::next())?->status) {
                self::deactivateCollegist($user);
            }
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
