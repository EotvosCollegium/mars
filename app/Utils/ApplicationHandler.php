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
     * @return Carbon the application deadline set in .env
     */
    public static function getApplicationDeadline(): Carbon
    {
        return Carbon::parse(config('custom.application_deadline'));
    }

    /**
     * @return bool if the deadline has been extended or not
     */
    public static function isDeadlineExtended(): bool
    {
        return config('custom.application_extended');
    }


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
            'semester_average' => 'nullable',
            'competition' => 'nullable',
            'publication' => 'nullable',
            'foreign_studies' => 'nullable',
            'question_1' => 'nullable|array',
            'question_1.*' => 'string',
            'question_2' => 'nullable|string',
            'question_3' => 'nullable|string',
            'question_4' => 'nullable|string',
            'present' => 'nullable|string',
            'accommodation' => 'nullable|in:on'
        ]);

        $data['applied_for_resident_status'] = $request->input('status') == "resident";
        $data['accommodation'] = $request->input('accommodation') === "on";

        Application::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
    }

    /**
     * @param Request $request
     * @param $user
     * @return void
     */
    public function storeFiles(Request $request, $user): void
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2000',
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

        Storage::delete($file->path);
        $file->delete();
    }

    /**
     * @param Request $request
     * @param $user
     * @return void
     */
    public function storeProfilePicture(Request $request, $user): void
    {
        $request->validate([
            'picture' => 'required|mimes:jpg,jpeg,png|max:2000',
        ]);
        $path = $request->file('picture')->store('avatars');
        $old_profile = $user->profilePicture;
        if ($old_profile) {
            Storage::delete($old_profile->path);
            $old_profile->update(['path' => $path]);
        } else {
            $user->profilePicture()->create(['path' => $path, 'name' => 'profile_picture']);
        }
    }
}
