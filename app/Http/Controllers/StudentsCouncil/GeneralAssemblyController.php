<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\Question;
use App\Models\QuestionOption;

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
     * Returns a page with only the code of a general_assembly with a large font.
     */
    public function showCode(GeneralAssembly $general_assembly)
    {
        $this->authorize('administer', GeneralAssembly::class);

        return view('student-council.general-assemblies.show-code', [
            "general_assembly" => $general_assembly,
            "passcode" => GeneralAssembly::getTemporaryPasscode()
        ]);
    }

    /**
     * Opens a GeneralAssembly.
     */
    public function openAssembly(GeneralAssembly $general_assembly)
    {
        $this->authorize('administer', GeneralAssembly::class);
        if ($general_assembly->hasBeenOpened()) {
            abort(401, "tried to open a general_assembly which has already been opened");
        }
        $general_assembly->open();
        return back()->with('message', __('voting.sitting_opened'));
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
