<?php

namespace App\Mail;

use App\Models\Internet\Router;
use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RouterWarningResident extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $recipient;
    public Router $router;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipient, $router)
    {
        $this->recipient = $recipient;
        $this->router = $router;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.router_status_warning_resident')
                    ->subject(__('mail.router_status_warning'));
    }
}
