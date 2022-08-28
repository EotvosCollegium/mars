<?php

namespace App\Http\Controllers\Secretariat;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;



use App\Http\Controllers\Controller;
use App\Models\EventTrigger;
use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\User;
use App\Models\Role;

// TODO: rename this class
class SecretariatController extends Controller
{
    public function list()
    {
        return Semester::current()->activeUsers;
    }

    public static function isStatementAvailable()
    {
        $statement_event = EventTrigger::find(EventTrigger::INTERNET_ACTIVATION_SIGNAL)->date;
        $deadline_event = EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->date;
        // If the deadline is closer than sending out the request, that means
        // the request has been already sent out.
        return $deadline_event < $statement_event;
    }

    public static function showStatusUpdate()
    {
        //TODO policy
        if (Auth::user()->getStatusIn(Semester::previous()) == SemesterStatus::DEACTIVATED) {
            abort(403);
        }
        return view('secretariat.statuses.status_update_form');
    }

    public static function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'semester_status' => 'required|in:' . SemesterStatus::ACTIVE . ',' . SemesterStatus::PASSIVE . ',' . SemesterStatus::DEACTIVATED,
            'collegist_role' => 'required|in:resident,extern'
        ]);
        $validator->validate();

        $user = Auth::user();
        $user->setStatus($request->semester_status);
        $user->setCollegistRole($request->collegist_role);
        return back()->with('message', 'general.success');
    }

    public static function sendStatementMail()
    {
        $users = User::collegists();
        foreach ($users as $user) {
            if ($user->getStatusIn(Semester::previous()) == SemesterStatus::DEACTIVATED) {
                SemesterStatus::withoutEvents(function () use ($user) {
                    $user->setStatus(SemesterStatus::DEACTIVATED, 'Was deactivated in last semester');
                });
            } else {
                SemesterStatus::withoutEvents(function () use ($user) {
                    $user->setStatus(SemesterStatus::INACTIVE, 'New semester started');
                });
                Mail::to($user)->queue(new \App\Mail\StatusStatementRequest($user->name));
            }
        }
    }

    /**
     * Those who did not make their statements by now will be inactive
     * next semester.
     */
    public static function finalizeStatements()
    {
        $users = User::collegists();
        $current_semester = Semester::current();
        foreach ($users as $user) {
            if (! $user->isInSemester($current_semester->id) || $user->getStatus() == SemesterStatus::INACTIVE) {
                $user->setStatus(SemesterStatus::DEACTIVATED, 'Failed to make a statement');
            }
        }
    }
}
