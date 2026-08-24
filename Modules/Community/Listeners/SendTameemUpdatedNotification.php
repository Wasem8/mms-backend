<?php

namespace Modules\Community\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Community\Events\TameemUpdated;
use Modules\User\Models\User;

class SendTameemUpdatedNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(TameemUpdated $event): void
    {
        $recipients = User::whereIn('id', $event->recipientIds)->get();

        foreach ($recipients as $recipient) {
            $this->notificationService->notify(
                $recipient,
                'تحديث تعميم: ' . $event->tameem->title,
                $event->tameem->content,
                'tameem_updated',
                ['tameem_id' => (string) $event->tameem->id]
            );
        }
    }
}
