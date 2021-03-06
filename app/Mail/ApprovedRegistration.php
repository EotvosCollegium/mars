<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovedRegistration extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $recipent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipent)
    {
        $this->recipent = $recipent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.approved_registration')
                    ->subject(__('mail.approved_registration'));
    }
}
