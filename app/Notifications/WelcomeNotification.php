<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::broker()->createToken($notifiable);

        $url = route('password.set', [
            'token' => $token,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject('Your CDRRMO DMS account is ready')
            ->greeting("Hi {$notifiable->name},")
            ->line('An account has been created for you on the CDRRMO Document Management System.')
            ->line("Your login email: {$notifiable->email}")
            ->action('Set your password', $url)
            ->line('This link is valid for 3 days. If it expires, ask your administrator to resend your welcome email.');
    }
}
