<?php

namespace App\Observers;

use App\Mail\LanguageExamUploaded;
use App\Models\LanguageExam;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;

class LanguageExamObserver
{
    /**
     * Handle the LanguageExam "created" event: notify the secretariat that a new language exam has been uploaded.
     */
    public function created(LanguageExam $languageExam): void
    {
        $user = $languageExam->educationalInformation->user;
        if (!$user->verified) {
            // No need to notify the secretariat if the user is still an applicant.
            return;
        }

        $secretaries = User::withRole(Role::SECRETARY)->get();
        foreach ($secretaries as $recipient) {
            Mail::to($recipient)->queue(new LanguageExamUploaded($recipient, $languageExam));
        }
    }

    /**
     * Handle the LanguageExam "deleted" event.
     */
    public function deleted(LanguageExam $languageExam): void
    {
        //
    }
}
