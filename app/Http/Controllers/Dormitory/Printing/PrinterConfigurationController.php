<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Http\Controllers\Controller;
use App\Mail\NoPaper;
use App\Models\PrintAccount;
use App\Models\PrinterConfiguration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PrinterConfigurationController extends Controller
{
    /**
     * Returns the print page.
     * @return View
     */
    public function index()
    {
        return view('dormitory.print.app', [
            "users" => User::all(),
            "user" => user(),
            "printer_configurations" => PrinterConfiguration::all(),
        ]);
    }

    /**
     * Returns the admin print page.
     * @return View
     */
    public function adminIndex()
    {
        $this->authorize('handleAny', PrintAccount::class);

        return view('dormitory.print.manage.app', ["users" => User::all()]);
    }

    /**
     * Handles reports of the printer running out of paper and printer configuration (de)activation
     */
    public function update(Request $request, PrinterConfiguration $printer_configuration)
    {
        if ($request->has("no_paper")) {
            $this->authorize('reportError', $printer_configuration);
            if ($printer_configuration->paper_out_at === null || now()->diffInMinutes($printer_configuration->paper_out_at, true) > 30) {
                Mail::to(User::withRole(Role::SYS_ADMIN)->get())->queue(new NoPaper(user()->name));
                $printer_configuration->update(['paper_out_at' => now()]);
            }
            return redirect()->back()->with('message', __('mail.email_sent'));
        }
        if ($request->has("activate") || $request->has("deactivate")) {
            $this->authorize('manageActivation', $printer_configuration);
            $printer_configuration->update([
                'active' => $request->has("activate")
            ]);
            return redirect()->back()->with('message', __('general.successful_modification'));
        }
        abort(400);
    }
}
