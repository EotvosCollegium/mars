<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StatusDeactivated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $recipient;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.status_deactivated')
            ->subject('Státuszod deaktivált lett');
    }
}
