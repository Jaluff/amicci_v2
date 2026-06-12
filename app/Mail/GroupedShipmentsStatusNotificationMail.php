<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GroupedShipmentsStatusNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Collection $shipments
     * @param string $status
     * @param string $recipient
     */
    public function __construct(
        public Collection $shipments,
        public string $status,
        public string $recipient
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->shipments->count() === 1) {
            $subject = "Actualización de Envío: Guía #{$this->shipments->first()->numero} - {$this->status}";
        } else {
            $subject = "Actualización de Envíos: Múltiples Guías ({$this->shipments->count()}) - {$this->status}";
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.grouped_shipments_status_notification',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
