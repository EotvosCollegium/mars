<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * This is sent to users who have active reservations
 * for an item that has been just tagged
 * as out-of-order or as functioning.
 */
class AffectedReservation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public bool $outOfOrder;
    public string $itemName;
    public string $itemType;
    public string $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(bool $outOfOrder, string $itemName, string $itemType, string $recipient)
    {
        $this->outOfOrder = $outOfOrder;
        $this->itemName = $itemName;
        $this->itemType = $itemType;
        $this->recipient = $recipient;
    }

    /**
     * Whether the message is about a washing machine.
     */
    private function isForWashingMachine(): bool
    {
        return \App\Models\ReservableItem::WASHING_MACHINE == $this->itemType;
    }

    /**
     * The message subject.
     */
    public function makeSubject(): string
    {
        if ($this->outOfOrder) {
            return
                $this->isForWashingMachine()
                ? 'Hibás mosógép'
                : 'Használhatatlan terem';
        } else {
            return
                'Javított ' .
                ($this->isForWashingMachine()
                 ? 'mosógép' : 'terem');
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.affected_reservation')
            ->subject($this->makeSubject());
    }
}
