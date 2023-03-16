<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\EventTrigger;
use App\Models\Role;
use App\Models\SemesterStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SemesterController extends Controller
{
    public static function isStatementAvailable()
    {
        $statement_event = EventTrigger::find(EventTrigger::SEND_STATUS_STATEMENT_REQUEST)->date;
        $deadline_event = EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->date;
        // If the deadline is closer than sending out the request, that means
        // the request has been already sent out.
        return $deadline_event < $statement_event;
    }

    public function showStatusUpdate()
    {
        $this->authorize('is-collegist');

        /* @var User $user */
        $user = Auth::user();
        if (!self::isStatementAvailable()) {
            return redirect('home')->with('error', 'Lejárt a határidő a collegiumi státusz beállítására. Keresd fel a titkárságot vagy a rendszergazdákat.');
        }
        return view('secretariat.statuses.status_update_form');
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('is-collegist');

        $validator = Validator::make($request->all(), [
            'semester_status' => 'required|in:' . SemesterStatus::ACTIVE . ',' . SemesterStatus::PASSIVE . ',' . Role::ALUMNI,
            'comment' => 'nullable|string',
            'resign_residency' => 'nullable'
        ]);
        $validator->validate();

        /* @var User $user */
        $user = Auth::user();
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
     * Send out the request to make the status statement.
     */
    public static function sendStatementMail()
    {
        Mail::to(env('MAIL_MEMBRA'))->queue(new \App\Mail\StatusStatementRequest());
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
