<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InternetFault extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $recipient;
    public string $reporter;
    public string $report;
    public string $error_message;
    public string $when;
    public string $tries;
    public string $user_os;
    public string $room;
    public string $availability;
    public bool $can_enter;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string  $recipient,
        string  $reporter,
        string  $report,
        ?string $error_message,
        string  $when,
        ?string $tries,
        ?string $user_os,
        ?string $room,
        ?string $availability,
        ?bool   $can_enter
    ) {
        $this->recipient = $recipient;
        $this->reporter = $reporter;
        $this->report = $report;
        $this->error_message = $error_message ?? '';
        $this->when = $when;
        $this->tries = $tries ?? '';
        $this->user_os = $user_os ?? '';
        $this->room = $room ?? '';
        $this->availability = $availability ?? '';
        $this->can_enter = $can_enter ?? false;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.internet_fault')
            ->subject(__('mail.report-internet-fault-subject'));
    }
}
