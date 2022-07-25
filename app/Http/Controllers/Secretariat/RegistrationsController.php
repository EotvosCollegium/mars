<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegistrationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:registration.handle');
    }

    public function index()
    {
        $users = User::withoutGlobalScope('verified')
            ->where('verified', false)
            ->whereDoesntHave('roles', function (Builder $query) {
                $query->where('name', Role::COLLEGIST);
            })
            ->with(['personalInformation'])
            ->get();

        return view('secretariat.registrations.list', ['users' => $users]);
    }

    public function accept(Request $request)
    {
        $user = User::withoutGlobalScope('verified')->findOrFail($request->id);
        if ($user->verified) {
            return redirect()->route('secretariat.registrations');
        }

        $user->update(['verified' => true]);
        if ($user->hasRole(Role::TENANT)) {
            $date = (Carbon::now()->addMonths(6)->gt($user->personalInformation->tenant_until.' 00:00:00')) ? ($user->personalInformation->tenant_until.' 00:00:00') : Carbon::now()->addMonths(6);
            $user->internetAccess()->update(['has_internet_until' => $date]);
        }

        Cache::decrement('user');

        // Send notification mail.
        Mail::to($user)->queue(new \App\Mail\ApprovedRegistration($user->name));

        return redirect()->route('secretariat.registrations')->with('message', __('general.successful_modification'));
    }

    public function reject(Request $request)
    {
        $user = User::withoutGlobalScope('verified')->findOrFail($request->id);
        if ($user->verified) {
            return redirect()->route('secretariat.registrations');
        }

        $user->delete();

        Cache::decrement('user');

        return redirect()->route('secretariat.registrations')->with('message', __('general.successful_modification'));
    }

    public function invite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => ['required', 'unique:users,email'],
        ]);
        $validator->validate();

        $user = User::firstWhere('email', $request->email);
        if(!$user)
        {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Hash::make(\Str::random(32)),
                'verified' => true,
            ]);
        }

        \Invytr::invite($user);
        return redirect()->route('secretariat.permissions.show', ['id' => $user->id])->with('message', __('registration.set_permissions'));
    }

    public function show(Request $request)
    {
        $user = User::withoutGlobalScope('verified')->findOrFail($request->id);
        if ($user->verified) {
            return redirect()->route('secretariat.registrations');
        }

        $unverified_users_left = count(User::withoutGlobalScope('verified')->where('verified', false)->get());

        return view('secretariat.registrations.show', ['user' => $user, 'users_left' => $unverified_users_left]);
    }
}
