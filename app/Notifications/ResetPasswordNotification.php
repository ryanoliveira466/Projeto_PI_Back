<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $frontendUrl = 'http://127.0.0.1:5501/reset-password.html'; // Change to your frontend URL

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->line('Click the button below to reset your password.')
            ->action('Reset Password', "{$frontendUrl}?token={$this->token}&email={$notifiable->getEmailForPasswordReset()}")
            ->line('If you did not request a password reset, no further action is required.');
    }
}
