<?php

namespace App\Application\Notifications;

use App\Data\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification
{
    public function __construct(private readonly string $token)
    {
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = url('/some-path-to-handle-password-reset/?token='. $this->token . '&email=' . $notifiable->email);

        return (new MailMessage)
            ->subject('XmlShipFl. Reset password')
            ->greeting("Hello {$notifiable->name}!")
            ->line('We received a request for your password to be reset')
            ->action('Reset password', $url)
            ->line('Token: '. $this->token)
            ->line('Or use the token to POST email/password/password_confirmation/token ' . route('auth.new.password'))
            ->line('Thank you for using our application!');
    }

    public function via(User $notifiable): array
    {
        return ['mail'];
    }
}
