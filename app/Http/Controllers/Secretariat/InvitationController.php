<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Mail\Invitation;
use App\Models\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    /**
     * Create and send a new invitation
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('invite', User::class);

        $request->validate([
            'name' => 'required',
            'email' => ['required', 'unique:users,email'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(32)),
            'verified' => true,
        ]);

        $token = $user->generatePasswordResetToken();
        Mail::to($user)->send(new Invitation($user, $token));

        return redirect()->route('users.show', ['user' => $user->id])->with('message', __('registration.set_permissions'));

    }

}
