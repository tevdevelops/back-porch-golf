<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your bay is booked: '.$this->booking->localStart()->format('D M j, g:i A'),
        );
    }

    public function content(): Content
    {
        $start = $this->booking->localStart();

        return new Content(
            markdown: 'mail.booking-confirmed',
            with: [
                'bayName' => $this->booking->bay->name,
                'when' => $start->format('l, F j').' · '.$start->format('g:i A').' – '.$this->booking->localEnd()->format('g:i A'),
                'doorOpens' => $this->booking->accessStartsAt()->setTimezone(config('studio.timezone'))->format('g:i A'),
                'manageUrl' => $this->booking->manageUrl(),
                'total' => number_format($this->booking->total_cents / 100, 2),
            ],
        );
    }
}
