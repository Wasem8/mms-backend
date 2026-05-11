<?php

namespace Modules\User\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOTPNotification extends Notification
{
    protected $otp;
    protected $type;

    public function __construct($otp, $type = 'verification')
    {
        $this->otp = $otp;
        $this->type = $type;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = $this->type === 'reset'
            ? 'Password Reset OTP'
            : 'Email Verification OTP';

        $message = $this->type === 'reset'
            ? 'Use the following OTP to reset your password:'
            : 'Use the following OTP to verify your email:';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name)
            ->line($message)
            ->line('**' . $this->otp . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this, please ignore this email.');
    }
}
