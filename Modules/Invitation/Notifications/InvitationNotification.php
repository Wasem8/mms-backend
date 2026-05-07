<?php

namespace Modules\Invitation\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Invitation\Models\Invitation;

class InvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public Invitation $invitation) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You are invited to join MMS')
            ->greeting('Hello!')
            ->line('You have been invited to join our system.')
            ->line('Role: ' . $this->invitation->role)
            ->action('Accept Invitation', url('/api/invitations/accept?token=' . $this->invitation->token))
            ->line('This invitation will expire soon.');
    }
}
