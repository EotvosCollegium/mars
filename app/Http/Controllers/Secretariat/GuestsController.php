<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Mail\ApprovedRegistration;
use App\Models\Faculty;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GuestsController extends Controller
{
    /**
     * Display the list of users waiting for approval.
     * @return View
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('handleGuests', User::class);
        $users = User::withoutGlobalScope('verified')
            ->where('verified', false)
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', Role::TENANT);
            })
            ->with(['personalInformation'])
            ->get();

        return view('secretariat.registrations.list', ['users' => $users]);
    }

    /**
     * Verify a new user.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     */
    public function accept(Request $request)
    {
        $this->authorize('handleGuests', User::class);

        $user = User::withoutGlobalScope('verified')->findOrFail($request->id);
        if ($user->verified) {
            return redirect()->route('secretariat.registrations');
        }

        $user->update(['verified' => true]);
        if ($user->hasRole(Role::TENANT)) {
            $date = min(Carbon::now()->addMonths(6), Carbon::parse($user->personalInformation->tenant_until));
            $user->internetAccess()->update(['has_internet_until' => $date]);
            $user->personalInformation()->update(['tenant_until' => $date]);
        }

        Cache::decrement('user');

        Mail::to($user)->queue(new ApprovedRegistration($user->name));

        return redirect()->route('secretariat.registrations')->with('message', __('general.successful_modification'));
    }

    /**
     * Reject and delete a new user.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     */
    public function reject(Request $request)
    {
        $this->authorize('handleGuests', User::class);

        $user = User::withoutGlobalScope('verified')->findOrFail($request->id);
        if ($user->verified) {
            return redirect()->route('secretariat.registrations');
        }

        $user->delete();

        Cache::decrement('user');

        return redirect()->route('secretariat.registrations')->with('message', __('general.successful_modification'));
    }

}
