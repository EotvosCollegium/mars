<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationFileUploaded extends Mailable
{
    use Queueable;
    use SerializesModels;

    public Application $application;
    public string $fileName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $fileName, Application $application)
    {
        $this->application = $application;
        $this->fileName = $fileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.application_file_uploaded')
                    ->subject('Egy felvételiző adatai módosultak');
    }
}
