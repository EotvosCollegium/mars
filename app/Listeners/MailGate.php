<?php

namespace App\Listeners;

use Illuminate\Support\Facades\App;

class MailGate
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // Prevent the propagation of the event here, therefore stopping the mail.
        if ((config('app.debug') && !config('mail.active')) || App::environment() == 'testing') {
            return false;
        }
    }
}
