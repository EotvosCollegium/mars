<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Invitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $recipient;
    public string $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $recipient, string $token)
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
