<?php

namespace Modules\Community\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Community\Events\SermonApproved;

class SendSermonApprovedNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(SermonApproved $event): void
    {
        $mosqueManager = $event->sermon->mosqueManager;

        if (!$mosqueManager) {
            return;
        }

        $this->notificationService->notify(
            $mosqueManager,
            'تم اعتماد الخطبة',
            "تم اعتماد خطبتك \"{$event->sermon->title}\" وأصبحت متاحة في الأرشيف.",
            'sermon_approved',
            ['sermon_id' => (string) $event->sermon->id]
        );
    }
}
