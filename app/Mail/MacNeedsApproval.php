<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MacNeedsApproval extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $recipient;
    public string $reporter;

    /**
     * Create a new message instance.
     */
    public function __construct(string $recipient, string $reporter)
    {
        $this->recipient = $recipient;
        $this->reporter = $reporter;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.mac_needs_approval')
            ->subject('MAC cím jóváhagyásra vár');
    }
}
