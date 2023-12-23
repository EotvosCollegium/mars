<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Mail\InternetFault;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InternetController extends Controller
{

    /**
     * Show the internet access page.
     * @return View
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(): View
    {
        $internetAccess = user()->internetAccess()->with('macAddresses')->first();

        $this->authorize('handle', $internetAccess);

        return view('network.internet.app', ['internet_access' => $internetAccess]);
    }


    /**
     * Resets the Wi-Fi password.
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function resetWifiPassword(): RedirectResponse
    {
        $internetAccess = user()->internetAccess;

        $this->authorize('handle', $internetAccess);

        $internetAccess->resetPassword();

        return redirect()->back();
    }

    /**
     * Sends an email to all admins with the report of a fault.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthenticationException
     */
    public function reportFault(Request $request): RedirectResponse
    {
        $request->validate([
            'report' => 'required|string',
            'user_os' => 'required|string',
        ]);

        foreach (User::admins() as $admin) {
            Mail::to($admin)->queue(new InternetFault($admin->name, user()->name, $request->get('report'), $request->get('user_os')));
        }
        return redirect()->back()->with('message', __('mail.email_sent'));
    }


}
