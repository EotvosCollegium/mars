<?php

namespace App\Http\Controllers;

use App\Models\SemesterSetting;
use App\Enums\SemesterSettingType;
use Illuminate\Http\Request;

class SemesterSettingController extends Controller
{
    public function index()
    {
        $settings = SemesterSetting::all();
        return view('semester-settings.index', compact('settings'));
    }

    public function edit(SemesterSetting $semesterSetting)
    {
        $setting = $semesterSetting->first();

        //generate the next semester
        $setting->semester->succ();

        return view('semester-settings.edit', ['setting' => $setting]);
    }

    public function update(Request $request, SemesterSetting $semesterSetting)
    {
        $validatedData = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $semesterSetting->update([
            'semester_id' => $validatedData['semester_id'],
        ]);

        return redirect()->route('semester_settings.index')->with('success', 'Semester setting updated successfully.');
    }
}