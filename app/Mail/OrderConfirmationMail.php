<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public array $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing('items');
        $this->settings = Setting::pluck('value', 'key')->toArray();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $restaurantName = $this->settings['restaurant_name'] ?? config('app.name', 'Rotisería La Abuela');

        return new Envelope(
            subject: "¡Confirmación de tu Pedido #{$this->order->id} - {$restaurantName}! 👵🍕",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_confirmation',
        );
    }
}
