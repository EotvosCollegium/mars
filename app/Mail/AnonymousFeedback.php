<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnonymousFeedback extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $recipent;
    public $feedback;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipent, $feedback)
    {
        $this->recipent = $recipent;
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
