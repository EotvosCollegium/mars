<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Http\Controllers\Controller;
use App\Mail\NoPaper;
use App\Models\PrintAccount;
use App\Models\Printer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PrinterController extends Controller
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
            "printer" => Printer::firstWhere('name', config('print.printer_name')),
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
     * Sets the given printer's out of paper sign.
     */
    public function update(Request $request, Printer $printer)
    {
        $request->validate([
            "no_paper" => "boolean",
        ]);

        if ($request->boolean("no_paper")) {
            if ($printer->paper_out_at === null || now()->diffInMinutes($printer->paper_out_at) > 30) {
                Mail::to(User::withRole(Role::SYS_ADMIN)->get())->queue(new NoPaper(user()->name));
            }
            $printer->update(['paper_out_at' => now()]);
            return redirect()->back()->with('message', __('mail.email_sent'));
        } else {
            $this->authorize('handleAny', PrintAccount::class);
            $printer->update([
                "paper_out_at" => null,
            ]);
            return redirect()->back()->with('message', __('general.successful_modification'));
        }
    }
}
