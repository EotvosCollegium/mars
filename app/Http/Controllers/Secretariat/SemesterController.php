<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\EventTrigger;
use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SemesterController extends Controller
{
    public static function isStatementAvailable($user)
    {
        $statement_event = EventTrigger::find(EventTrigger::SEND_STATUS_STATEMENT_REQUEST)->date;
        $deadline_event = EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->date;
        // If the deadline is closer than sending out the request, that means
        // the request has been already sent out.
        return $deadline_event < $statement_event || !$user->hasActivated();
    }

    public function showStatusUpdate()
    {
        $this->authorize('is-collegist');

        /* @var User $user */
        $user = Auth::user();
        if ($user->getStatusIn(Semester::previous()->id) == SemesterStatus::DEACTIVATED) {
            abort(403);
        }
        if (!self::isStatementAvailable($user)) {
            return redirect('home')->with('message', 'A státuszodat már nem tudod frissíteni a félévben. Már megadtad a státuszodat és lejárt a határidő is.');
        }
        return view('secretariat.statuses.status_update_form');
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('is-collegist');

        $validator = Validator::make($request->all(), [
            'semester_status' => 'required|in:' . SemesterStatus::ACTIVE . ',' . SemesterStatus::PASSIVE . ',' . SemesterStatus::DEACTIVATED,
            'collegist_role' => 'required|in:resident,extern'
        ]);
        $validator->validate();

        /* @var User $user */
        $user = Auth::user();
        $user->setStatus($request->semester_status, "Státusz bejelentés");
        $user->setCollegist($request->collegist_role);
        return redirect('home')->with('message', __('general.successful_modification'));
    }

    public static function sendStatementMail()
    {
        $users = User::collegists();
        foreach ($users as $user) {
            if ($user->getStatus() != SemesterStatus::INACTIVE /* default */) {
                continue;
            }
            if ($user->getStatus() != SemesterStatus::DEACTIVATED) {
                continue;
            }
            if ($user->getStatusIn(Semester::previous()) == SemesterStatus::DEACTIVATED) {
                SemesterStatus::withoutEvents(function () use ($user) {
                    $user->setStatus(SemesterStatus::DEACTIVATED, 'Was deactivated in last semester');
                });
                continue;
            }
            SemesterStatus::withoutEvents(function () use ($user) {
                $user->setStatus(SemesterStatus::INACTIVE, 'Default status');
            });
        }

        Mail::to(env('MAIL_MEMBRA'))->queue(new \App\Mail\StatusStatementRequest());
    }

    /**
     * Those who did not make their statements by now will be deactivated
     * next semester.
     */
    public static function finalizeStatements()
    {
        $users = User::collegists();
        $current_semester = Semester::current();
        foreach ($users as $user) {
            if (! $user->isInSemester($current_semester->id) || $user->getStatus() == SemesterStatus::INACTIVE) {
                $user->setStatus(SemesterStatus::DEACTIVATED, 'Failed to make a statement.');
            }
        }
    }
}
