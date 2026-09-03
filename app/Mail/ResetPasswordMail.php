<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $token;
    public string $resetUrl;
    public array $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $this->settings = Setting::pluck('value', 'key')->toArray();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $restaurantName = $this->settings['restaurant_name'] ?? config('app.name', 'Rotisería La Abuela');

        return new Envelope(
            subject: "🔐 Restablecer tu Contraseña - {$restaurantName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password',
        );
    }
}
