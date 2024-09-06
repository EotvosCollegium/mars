<?php

namespace App\Mail\Reservations;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservations\ReservableItem;

/**
 * This is sent to users who have active reservations
 * for an item that has been just tagged
 * as out-of-order or as functioning.
 */
class ReservationAffected extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ReservableItem $item;
    public string $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(ReservableItem $item, string $recipient)
    {
        $this->item = $item;
        $this->recipient = $recipient;
    }

    /**
     * Whether the message is about a washing machine.
     */
    private function isForWashingMachine(): bool
    {
        return $this->item->isWashingMachine();
    }

    /**
     * The message subject.
     */
    public function makeSubject(): string
    {
        if ($this->item->out_of_order) {
            return
                $this->isForWashingMachine()
                ? __('reservations.faulty_washing_machine')
                : __('reservations.faulty_room');
        } else {
            return
                $this->isForWashingMachine()
                ? __('reservations.repaired_washing_machine')
                : __('reservations.repaired_room');
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.reservations.reservation_affected')
            ->subject($this->makeSubject());
    }
}
