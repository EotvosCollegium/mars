<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnonymousFeedback extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $recipient;
    public $feedback;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipient, $feedback)
    {
        $this->recipient = $recipient;
        $this->feedback = $feedback;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.anonymous_feedback')
                    ->subject(__('mail.anonymous_feedback'));
    }
}
