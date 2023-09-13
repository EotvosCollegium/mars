<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\GeneralAssemblies\Question;
use App\Models\GeneralAssemblies\QuestionOption;

class GeneralAssemblyController extends Controller
{
    /**
     * Lists general_assemblies.
     */
    public function index()
    {
        $this->authorize('viewAny', GeneralAssembly::class);
        return view('student-council.general-assemblies.index', [
            "general_assemblies" => GeneralAssembly::orderByDesc('opened_at')->get()
        ]);
    }

    /**
     * Returns the 'new GeneralAssembly' page.
     */
    public function create()
    {
        $this->authorize('administer', GeneralAssembly::class);
        return view('student-council.general-assemblies.create');
    }

    /**
     * Saves a new GeneralAssembly.
     */
    public function store(Request $request)
    {
        $this->authorize('administer', GeneralAssembly::class);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);
        $validator->validate();

        $general_assembly = GeneralAssembly::create([
            'title' => $request->title,
            'opened_at' => now(),
        ]);

        return view('student-council.general-assemblies.show', [
            "general_assembly" => $general_assembly,
            "passcode" => GeneralAssembly::getTemporaryPasscode()
        ]);
    }

    /**
     * Returns a page with the details and questions of a general_assembly.
     */
    public function show(GeneralAssembly $general_assembly)
    {
        $this->authorize('viewAny', GeneralAssembly::class);

        return view('student-council.general-assemblies.show', [
            "general_assembly" => $general_assembly,
            "passcode" => GeneralAssembly::getTemporaryPasscode()
        ]);
    }

    /**
     * Closes a general_assembly.
     */
    public function closeAssembly(GeneralAssembly $general_assembly)
    {
        $this->authorize('administer', GeneralAssembly::class);
        if (!$general_assembly->isOpen()) {
            abort(401, "tried to close a general_assembly which was not open");
        }
        $general_assembly->close();
        return back()->with('message', __('voting.sitting_closed'));
    }
}
