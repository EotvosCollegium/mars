<?php

namespace App\Mail\Reservations;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservations\ReservableItem;

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
    public ?string $message; // this is an explanation of the problem provided via a modal dialog

    /**
     * Create a new message instance.
     */
    public function __construct(ReservableItem $item, string $recipient, string $reporter, ?string $message)
    {
        $this->item = $item;
        $this->recipient = $recipient;
        $this->reporter = $reporter;
        $this->message = $message;
    }

    /**
     * Whether the message is about a washing machine.
     */
    private function isForWashingMachine(): bool
    {
        return \App\Enums\ReservableItemType::WASHING_MACHINE->value == $this->item->type;
    }

    /**
     * The message subject.
     */
    public function makeSubject(): string
    {
        if ($this->item->out_of_order) {
            $itemType = $this->isForWashingMachine() ? 'mosógép' : 'terem';
            return 'Javított ' . $itemType . ' jelentve';
        } else {
            return $this->isForWashingMachine()
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
        return $this->markdown('emails.reservations.report_reservable_item_fault')
            ->subject($this->makeSubject());
    }
}
