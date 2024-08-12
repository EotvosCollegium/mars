<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\ReservableItem;

/**
 * This is sent to admins and staff
 * when a reservable item is reported to be faulty.
 */
class ReportReservableItemFault extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ReservableItem $item;
    public string $recipient;
    public string $reporter;

    /**
     * Create a new message instance.
     */
    public function __construct(ReservableItem $item, string $recipient, string $reporter)
    {
        $this->item = $item;
        $this->recipient = $recipient;
        $this->reporter = $reporter;
    }

    /**
     * Whether the message is about a washing machine.
     */
    private function isForWashingMachine(): bool
    {
        return \App\Models\ReservableItem::WASHING_MACHINE == $this->item->type;
    }

    /**
     * The message subject.
     */
    public function makeSubject(): string
    {
        if ($this->item->out_of_order) {
            return
                'Javított ' .
                ($this->isForWashingMachine()
                 ? 'mosógép' : 'terem')
                 . ' jelentve';
        } else {
            return
                $this->isForWashingMachine()
                ? 'Hibás mosógép jelentve'
                : 'Használhatatlan terem jelentve';
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.report_reservable_item_fault')
            ->subject($this->makeSubject());
    }
}
