<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\EventTrigger;
use App\Models\Faculty;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\Role;
use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
        $this->authorize('is-collegist');
        if (!self::isEvaluationAvailable()) {
            return redirect('home')->with('error', 'Lejárt a határidő a kérdőív kitöltésére. Keresd fel a titkárságot.');
        }
        return view('secretariat.evaluation-form.app',[
            'user' => user(),
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all(),
            'general_assemblies' => GeneralAssembly::all()->sortByDesc('closed_at')->take(2),
            'community_services' => user()->communityServiceRequests()->where('semester_id', Semester::current()->id)->get(),
            'position_roles' => user()->roles()->whereIn('name', Role::STUDENT_POSTION_ROLES)->get(),
        ]);
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
        if ($request->semester_status == Role::ALUMNI) {
            self::deactivateCollegist($user);
        } else {
            $user->setStatus($request->semester_status, $request->comment);
            if ($request->has('resign_residency') && $user->isResident()) {
                $user->setExtern();
            }
        }

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
