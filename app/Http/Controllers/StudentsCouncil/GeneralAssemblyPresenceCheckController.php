<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Http\Controllers\Controller;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\GeneralAssemblies\PresenceCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralAssemblyPresenceCheckController extends Controller
{
    /**
     * Returns the 'new question' page.
     */
    public function create(GeneralAssembly $generalAssembly)
    {
        $this->authorize('administer', GeneralAssembly::class);

        if (!$generalAssembly->isOpen()) {
            abort(401, "tried to modify a general_assembly which was not open");
        }
        return view('student-council.general-assemblies.presence-checks.create', [
            "general_assembly" => $generalAssembly
        ]);
    }

    /**
     * Saves a new question.
     */
    public function store(Request $request, GeneralAssembly $generalAssembly)
    {
        $this->authorize('administer', GeneralAssembly::class);

        $data = $request->validate([
            'note' => 'nullable|string',
        ]);

        if (!$generalAssembly->isOpen()) {
            abort(401, "tried to modify a general assembly which was not open");
        }

        $presenceCheck = $generalAssembly->presenceChecks()->create($data + [
            'opened_at' => now(),
        ]);

        return redirect()->route('general_assemblies.presence_checks.show', [
            "general_assembly" => $generalAssembly,
            "presence_check" => $presenceCheck,
        ])->with('message', __('general.successful_modification'));
    }

    /**
     * Returns a page with the options (and results, if authorized) of a question.
     */
    public function show(GeneralAssembly $generalAssembly, $presenceCheck)
    {
        $this->authorize('viewAny', GeneralAssembly::class);
        $presenceCheck = $generalAssembly->presenceChecks()->findOrFail($presenceCheck);
        return view('student-council.general-assemblies.presence-checks.show', [
            "general_assembly" => $generalAssembly,
            "presence_check" => $presenceCheck,
        ]);
    }

    /**
     * Closes a presence check.
     */
    public function closePresenceCheck(GeneralAssembly $generalAssembly, $presenceCheck)
    {
        $this->authorize('administer', GeneralAssembly::class);
        $presenceCheck = $generalAssembly->presenceChecks()->findOrFail($presenceCheck);
        if (!$presenceCheck->isOpen()) {
            abort(401, "tried to close a question which was not open");
        }
        $presenceCheck->close();
        return back()->with('message', __('voting.presence_check_closed'));
    }

    /**
     * Saves a vote.
     */
    public function signPresence(Request $request, GeneralAssembly $generalAssembly, $presenceCheck)
    {
        /** @var PresenceCheck */
        $presenceCheck = $generalAssembly->presenceChecks()->findOrFail($presenceCheck);
        $this->authorize('signPresence', $presenceCheck); //this also checks whether the user has already voted

        $validator = Validator::make($request->all(), [
            'passcode' => 'string'
        ]);

        if (!GeneralAssembly::isTemporaryPasscode($request->passcode)) {
            $validator->after(function ($validator) {
                $validator->errors()->add('passcode', __('voting.incorrect_passcode'));
            });
        }
        $validator->validate();

        $presenceCheck->signPresence(user());

        return redirect()->route('general_assemblies.show', $presenceCheck->generalAssembly)->with('message', __('voting.successful_voting'));
    }
}
