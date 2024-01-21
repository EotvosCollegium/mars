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
    public string $user_os;

    /**
     * Create a new message instance.
     *
     * @param  string  $userName
     */
    public function __construct(string $recipient, string $reporter, string $report, string $user_os)
    {
        $this->recipient = $recipient;
        $this->reporter = $reporter;
        $this->report = $report;
        $this->user_os = $user_os;
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
