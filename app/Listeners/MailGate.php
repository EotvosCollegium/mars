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
     * If returns false, the event will not be propagated further.
     *
     * @param object $event
     * @return bool
     */
    public function handle($event)
    {
        // We can enable mails for tests as these are made fake emails in tests/TestCase.php
        if (App::environment() == 'testing') {
            return;
        }
        // Prevent the propagation of the event here, therefore stopping the mail.
        if ((config('app.debug') && !config('mail.active'))) {
            return false;
        }
    }
}
