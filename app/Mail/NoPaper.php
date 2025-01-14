<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NoPaper extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $reporter;

    /**
     * Create a new message instance.
     */
    public function __construct(string $reporter)
    {
        $this->reporter = $reporter;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.no_paper')
            ->subject('Kifogyott a papír a nyomtatóból');
    }
}
