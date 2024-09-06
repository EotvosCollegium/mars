<?php

namespace App\Mail\Reservations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservations\Reservation;

/**
 * A Mailable sent to a user
 * when an administrator has deleted one of their reservations
 * (if it has been unverified, we use the word 'reject').
 */
class ReservationDeleted extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** The name of the one who has approved the reservation. */
    public string $deleter;
    /** The name of the owner of the reservation (so the addressee). */
    public string $owner;
    /** The name of the item to which the reservation had belonged. */
    public string $itemName;
    /** The reservation in question, converted to an array
     *  (as it has probably been deleted, so the original model could not be used). */
    public array $reservationArray;
    /** Whether this had affected only one reservation or an entire group. */
    public bool $isForAll;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        string $deleter,
        string $owner,
        string $itemName,
        array $reservationArray,
        bool $isForAll = false
    ) {
        $this->deleter = $deleter;
        $this->owner = $owner;
        $this->itemName = $itemName;
        $this->reservationArray = $reservationArray;
        $this->isForAll = $isForAll;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject:
                __('reservations.' . ($this->reservationArray['verified']
                                        ? 'reservation_deleted'
                                        : 'reservation_rejected')),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.reservations.reservation_deleted'
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
