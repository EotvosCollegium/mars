<?php

namespace App\Mail;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EvaluationFormClosed extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $recipient;
    public ?array $deactivated;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $recipient, array $deactivated = null)
    {
        $this->recipient = $recipient;
        $this->deactivated = $deactivated;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.evaluation_form_closed')
            ->subject('Értesítés az év végi értékelő form eredményéről');
    }
}
