<?php

namespace App\Mail;

use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StatusUpdated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $recipient;
    public string $semester;
    public string $status;
    public ?string $comment;
    public ?User $modifier;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SemesterStatus $semesterStatus)
    {
        $this->recipient = $semesterStatus->user;
        $this->semester = $semesterStatus->semester->tag;
        $this->status = __('user.'.$semesterStatus->status);
        $this->comment = $semesterStatus->comment;
        $this->modifier = auth()->user();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.status_updated')
                    ->subject(__('mail.status_updated', ['semester' => $this->semester, 'status' => $this->status]));
    }
}
