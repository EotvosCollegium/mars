<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Invitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $recipient;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipient, $token)
    {
        $this->recipient = $recipient;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.invite')
                    ->subject('Meghívó az Eötvös Collegium tanulmányi rendszerébe');
    }
}
