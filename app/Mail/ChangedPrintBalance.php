<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChangedPrintBalance extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $recipient; //User model
    public $print_account_holder; //User model
    public $amount; //how much the balance has changed
    public $modifier; //modifier's name

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipient, $print_account_holder, $amount, $modifier)
    {
        $this->recipient = $recipient;
        $this->print_account_holder = $print_account_holder;
        $this->amount = $amount;
        $this->modifier = $modifier;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.changed_print_balance')
                    ->subject(__('print.changed_balance'));
    }
}
