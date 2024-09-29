<?php

namespace App\Http\Controllers\Auth;

use App\Exports\ApplicantsExport;
use App\Models\Application;
use App\Models\ApplicationForm;
use App\Models\Faculty;
use App\Models\File;
use App\Models\PeriodicEvent;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Semester;
use App\Models\User;
use App\Models\Workshop;
use App\Utils\ApplicationHandler;
use App\Utils\PeriodicEventController;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller handling the applicantion process.
 */
class ApplicationController extends PeriodicEventController
{
    use ApplicationHandler;

    private const EDUCATIONAL_ROUTE = 'educational';
    private const QUESTIONS_ROUTE = 'questions';
    private const FILES_ROUTE = 'files';
    private const DELETE_FILE_ROUTE = 'files.delete';
    private const SUBMIT_ROUTE = 'submit';

    public function __construct()
    {
        parent::__construct(PeriodicEvent::APPLICATION_PERIOD);
    }

    /**
     * Update the PeriodicEvent connected to the applications.
     * @throws AuthorizationException
     */
    public function updateApplicationPeriod(Request $request): RedirectResponse
    {
        $this->authorize('finalize', Application::class);

        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:now|after:start_date',
            'extended_end_date' => 'nullable|date|after:end_date',
        ]);

        $this->updatePeriodicEvent(
            Semester::find($request->semester_id),
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date),
            $request->extended_end_date ? Carbon::parse($request->extended_end_date) : null
        );

        return back()->with('message', __('general.successful_modification'));
    }

    /**
     * Return the view based on the request's page parameter.
     * @param Request $request
     * @return View
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (user()->hasRole(Role::TENANT)) {
            //let the user delete their tenant status
            return redirect()->route('users.tenant-update.show');
        }

        $this->ensureApplicationExists(user());

        // only allow access if the application period is open or after, if the user has submitted application
        if(!($this->isActive() || user()->application?->submitted)) {
            abort(403, "A felvétel jelenleg nincs megnyitva");
        }

        $data = [
            'workshops' => Workshop::all(),
            'faculties' => Faculty::all(),
            'is_active' => $this->isActive(),
            'deadline' => $this->getDeadline(),
            'deadline_extended' => $this->isExtended(),
            'user' => user(),
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
    public function store(Request $request): RedirectResponse
    {
        $user = user();

        if (!$this->isActive()) {
            return redirect()->route('application')->with('error', 'A jelentkezési határidő lejárt!');
        }
        $this->ensureApplicationExists($user);

        if ($user->application->submitted) {
            return redirect()->route('application')->with('error', 'Már véglegesítette a jelentkezését!');
        }

        switch ($request->input('page')) {
            //personal, educational data and profile picture update is in UserController
            case self::QUESTIONS_ROUTE:
                $this->storeQuestionsData($request, $user);
                break;
            case self::FILES_ROUTE:
                $this->storeFile($request, $user);
                break;
            case self::DELETE_FILE_ROUTE:
                $this->deleteFile($request, $user);
                break;
            case self::SUBMIT_ROUTE:
                return $this->submit($user);
            default:
                abort(404);
        }
        return redirect()->back()->with('message', __('general.successful_modification'));
    }


    /**
     * @param $user
     * @return RedirectResponse
     */
    public function submit(User $user): RedirectResponse
    {
        if (!$this->isActive()) {
            return redirect()->route('application')->with('error', 'A jelentkezési határidő lejárt');
        }
        $user->load('application'); // refresh
        if ($user->application->missingData() != []) {
            return back()->with('error', 'Hiányzó adatok!');
        }
        $user->application->update(['submitted' => true]);
        $user->internetAccess->setWifiCredentials($user->educationalInformation->neptun);
        $user->internetAccess->extendInternetAccess($this->getDeadline()?->addMonth());
        return back()->with('message', 'Sikeresen véglegesítette a jelentkezését!');
    }

    /**
     * Create the application for the user if it doesn't exist.
     * @param User $user
     */
    private function ensureApplicationExists(User $user): void
    {
        if ($user->application()->doesntExist()) {
            $user->application()->create();
        }
    }
}
