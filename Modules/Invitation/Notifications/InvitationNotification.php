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

        $acceptUrl = url('/invitations/accept?token=' . $this->invitation->token);

        $logoUrl = 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/images/logo.png';


        $this->invitation->loadMissing('mosque');

        return (new MailMessage)
            ->subject('دعوة للانضمام إلى منصة وَصْل التعليمية 🌟')
            ->view('invitation::invite', [
                'invitation' => $this->invitation,
                'acceptUrl'  => $acceptUrl,
                'logoUrl'    => $logoUrl
            ]);
    }
}
