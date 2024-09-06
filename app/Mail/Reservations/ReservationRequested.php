<?php

namespace App\Mail\Reservations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Reservations\Reservation;

class ReservationRequested extends Mailable
{
    use Queueable;
    use SerializesModels;

    public Reservation $reservation;
    public string $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation, string $recipient)
    {
        $this->reservation = $reservation;
        $this->recipient = $recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Jóváhagyásra váró teremfoglalás',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.reservation_requested',
        );
    }
}
