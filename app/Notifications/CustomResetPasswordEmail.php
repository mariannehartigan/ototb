<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;

class CustomResetPasswordEmail extends ResetPassword
{
    public function toMail($notifiable)
    {
      $url = url(route('password.reset', [
        'token' => $this->token,
        'email' => $notifiable->getEmailForPasswordReset(),
      ], false));

      return (new MailMessage)
        ->subject('Reset your password')
        ->markdown('reset-password-email', [
          'name' => $notifiable->name,
          'resetUrl' => $url,
        ]);
    }
}