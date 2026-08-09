<?php

namespace Modules\Community\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Community\Events\SermonRejected;
use Modules\User\Models\User;

class SendSermonRejectedNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(SermonRejected $event): void
    {
        $mosqueManager = User::find($event->mosqueManagerId);

        if (!$mosqueManager) {
            return;
        }

        $title = $event->isHardReject ? 'تم رفض الخطبة' : 'مطلوب تعديل على الخطبة';
        $reasonSuffix = $event->notes ? " - {$event->notes}" : '.';
        $body = $event->isHardReject
            ? "تم رفض خطبتك \"{$event->sermonTitle}\"" . $reasonSuffix
            : "يرجى تعديل خطبتك \"{$event->sermonTitle}\"" . $reasonSuffix;

        $this->notificationService->notify(
            $mosqueManager,
            $title,
            $body,
            $event->isHardReject ? 'sermon_rejected' : 'sermon_needs_edit',
            ['sermon_id' => (string) $event->sermonId]
        );
    }
}
