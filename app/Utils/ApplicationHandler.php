<?php

namespace App\Utils;

use App\Models\Application;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait ApplicationHandler
{
    /**
     * @param Request $request
     * @param User $user
     * @return void
     */
    public function storeQuestionsData(Request $request, User $user): void
    {
        $data = $request->validate([
            'status' => 'required|in:extern,resident',
            'graduation_average' => 'required|numeric',
            'semester_average' => 'nullable|array',
            'semester_average.*' => 'numeric',
            'competition' => 'nullable',
            'publication' => 'nullable',
            'foreign_studies' => 'nullable',
            'workshop' => 'nullable|array',
            'workshop.*' => 'nullable|exists:workshops,id',
            'question_1' => 'nullable|array',
            'question_1.*' => 'nullable|string',
            'question_2' => 'nullable|string',
            'question_3' => 'nullable|string',
            'question_4' => 'nullable|string',
            'present' => 'nullable|string',
            'accommodation' => 'nullable|in:on'
        ]);

        $data['applied_for_resident_status'] = $request->input('status') == "resident";
        $data['accommodation'] = $request->input('accommodation') === "on";

        $application = Application::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
        $application->syncAppliedWorkshops($request->input('workshop'));
    }

    /**
     * @param Request $request
     * @param $user
     * @return void
     */
    public function storeFile(Request $request, $user): void
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:' . config('custom.general_file_size_limit'),
            'name' => 'required|string|max:255',
        ]);
        $path = $request->file('file')->store('uploads');
        $user->application->files()->create(['path' => $path, 'name' => $request->input('name')]);
    }

    /**
     * @param Request $request
     * @param $user
     * @return void
     */
    public function deleteFile(Request $request, $user): void
    {
        $request->validate([
            'id' => 'required|exists:files',
        ]);

        $file = $user->application->files()->findOrFail($request->input('id'));

        $file->delete();
        Storage::delete($file->path);
    }
}
