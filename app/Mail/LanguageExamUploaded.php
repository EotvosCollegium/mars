<?php

namespace App\Mail;

use App\Models\LanguageExam;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LanguageExamUploaded extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $recipient;
    public User $modifier;
    public LanguageExam $languageExam;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $recipient, LanguageExam $languageExam)
    {
        $this->recipient = $recipient;
        $this->modifier = user();
        $this->languageExam = $languageExam;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.language_exam_uploaded')
            ->subject('Új nyelvvizsga került feltöltésre');
    }
}
