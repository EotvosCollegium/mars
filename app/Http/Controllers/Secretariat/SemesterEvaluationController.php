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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SemesterEvaluationController extends Controller
{
    /**
     * Check if the evaluation is available.
     * Based on the dates set in EventTriggers.
     */
    public static function isEvaluationAvailable()
    {
        $statement_event = EventTrigger::find(EventTrigger::SEND_STATUS_STATEMENT_REQUEST)->date;
        $deadline_event = EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->date;
        // If the deadline is closer than sending out the request, that means
        // the request has been already sent out.

        return true; //TODO delete
        return $deadline_event < $statement_event;
    }

    /**
     * Show the evaluation form.
     */
    public function show()
    {
        if (!self::isEvaluationAvailable()) {
            return redirect('home')->with('error', 'Lejárt a határidő a kérdőív kitöltésére. Keresd fel a titkárságot.');
        }
        return view('secretariat.evaluation-form.app',[
            'user' => user(),
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all(),
            'evaluation' => user()->semesterEvaluations()->where('semester_id', Semester::current()->id)->first(),
            'general_assemblies' => GeneralAssembly::all()->sortByDesc('closed_at')->take(2),
            'community_services' => user()->communityServiceRequests()->where('semester_id', Semester::current()->id)->get(),
            'position_roles' => user()->roles()->whereIn('name', Role::STUDENT_POSTION_ROLES)->get(),
        ]);
    }

    /**
     * Update form information.
     */
    public function store(Request $request)
    {
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
                    ['can_be_shared' => $request->has('can_be_shared')]));
                break;
            case 'feedback':
                if($request->has('anonymous_feedback') && $request->anonymous_feedback == 'on')
                    break;//TOOD
                else
                    $evaluation->update($request->only(['feedback']));
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
     * Update status information
     */
    public function storeStatus(Request $request)
    {
        $this->authorize('is-collegist');

        $validator = Validator::make($request->all(), [
            'semester_status' => 'required|in:' . SemesterStatus::ACTIVE . ',' . SemesterStatus::PASSIVE . ',' . Role::ALUMNI,
            'comment' => 'nullable|string',
            'resign_residency' => 'nullable'
        ]);
        $validator->validate();

        $user = user();


        return redirect('home')->with('message', __('general.successful_modification'));
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
            if (! $user->getStatus()?->status) {
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
