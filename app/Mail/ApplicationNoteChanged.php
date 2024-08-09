<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApplicationNoteChanged extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $modifier;
    public Application $application;
    public ?string $oldValue;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $modifier, Application $application, ?string $oldValue)
    {
        $this->modifier = $modifier;
        $this->application = $application;
        $this->oldValue = $oldValue;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.application_note_changed')
                    ->subject('Egy felvételiző adatai módosultak');
    }
}
