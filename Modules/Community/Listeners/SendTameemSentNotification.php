<?php

namespace Modules\Community\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Community\Events\TameemSent;
use Modules\User\Models\User;

class SendTameemSentNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(TameemSent $event): void
    {
        $recipients = User::whereIn('id', $event->recipientIds)->get();

        foreach ($recipients as $recipient) {
            $this->notificationService->notify(
                $recipient,
                'تعميم جديد: ' . $event->tameem->title,
                $event->tameem->content,
                'tameem_sent',
                ['tameem_id' => (string) $event->tameem->id]
            );
        }
    }
}
