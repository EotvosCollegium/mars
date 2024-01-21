<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Transactions extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $recipent;
    public $title;
    public $transactions;
    public $additional_message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipent, $transactions, $title, $additional_message = null)
    {
        $this->title= $title;
        $this->recipent = $recipent;
        $this->transactions = $transactions;
        $this->additional_message = $additional_message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.transactions')
                    ->subject($this->title);
    }
}
