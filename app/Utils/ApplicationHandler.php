<?php

namespace App\Utils;

use App\Models\Application;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            'status' => 'nullable|in:extern,resident',
            'graduation_average' => 'nullable|numeric|min:0',
            'semester_average' => 'nullable|array',
            'semester_average.*' => 'nullable|numeric|min:0',
            'competition' => 'nullable|array',
            'competition.*' => 'nullable|string',
            'publication' => 'nullable|array',
            'publication.*' => 'nullable|string',
            'foreign_studies' => 'nullable|array',
            'foreign_studies.*' => 'nullable|string',
            'workshop' => 'nullable|array',
            'workshop.*' => 'nullable|exists:workshops,id',
            'workshop_letter' => 'nullable|array',
            'workshop_letter.*' => 'nullable|string|max:1000',
            'question_1' => 'nullable|array',
            'question_1.*' => 'nullable|string',
            'question_2' => 'nullable|string',
            'question_3' => 'nullable|string',
            'question_4' => 'nullable|string',
            'present' => 'nullable|string',
            'accommodation' => 'sometimes|accepted',
            'publication_consent' => 'sometimes|accepted',
            'pseudonym' => ['string', 'min:5', 'max:20', 'regex:/^[A-Z]+$/', 'nullable', 'unique:App\Models\Application,pseudonym,' . $user->application->id],
        ]);

        if (!isset($data['status'])) {
            $data['applied_for_resident_status'] = null;
        } else {
            if ($data['status'] == "resident") {
                $data['applied_for_resident_status'] = true;
            }
            if ($data['status'] == "extern") {
                $data['applied_for_resident_status'] = false;
            }
        }

        $data['accommodation'] = isset($data['accommodation']) && $data['accommodation'];
        $data['publication_consent'] = isset($data['publication_consent']) && $data['publication_consent'];

        $workshopLetters = $data['workshop_letter'] ?? [];

        $application = Application::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
        $application->syncAppliedWorkshops($data['workshop'], $workshopLetters);
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
            'type' => ['required', Rule::enum(\App\Enums\FileType::class)],
        ]);
        $path = $request->file('file')->store('uploads');
        $user->application->files()->create(['path' => $path, 'type' => $request->input('type'), 'description' => $request->input('name')]);
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
