<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservation;

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
    /** The reservation in question. */
    public Reservation $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $deleter, Reservation $reservation)
    {
        $this->deleter = $deleter;
        $this->reservation = $reservation;
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
                $this->reservation->verified
                ? __('reservations.reservation_deleted')
                : __('reservations.reservation_rejected'),
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
            markdown: 'emails.reservation_deleted'
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
