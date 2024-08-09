<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Confirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $recipient;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipient)
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
        return $this->markdown('emails.register')
                    ->subject(__('registration.confirmed_signup'));
    }
}
