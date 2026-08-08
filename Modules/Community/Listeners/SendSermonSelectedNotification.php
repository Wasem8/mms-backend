<?php

namespace Modules\Community\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Community\Events\SermonSelectedForFriday;

class SendSermonSelectedNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(SermonSelectedForFriday $event): void
    {
        $sermon = $event->sermon;
        $originalMosqueManager = $sermon->mosqueManager;
        $deliveringMosqueManager = $event->deliveringMosqueManager;

        // Notify the original mosque manager, unless they selected their own sermon
        if ($originalMosqueManager && $originalMosqueManager->id !== $deliveringMosqueManager->id) {
            $this->notificationService->notify(
                $originalMosqueManager,
                'تم اختيار خطبتك ليوم الجمعة',
                "قام {$deliveringMosqueManager->name} باختيار خطبتك \"{$sermon->title}\" لإلقائها بتاريخ {$event->selection->friday_date}.",
                'sermon_selected',
                [
                    'sermon_id' => (string) $sermon->id,
                    'selection_id' => (string) $event->selection->id,
                    'delivering_mosque_manager_id' => (string) $deliveringMosqueManager->id,
                    'friday_date' => (string) $event->selection->friday_date,
                ]
            );
        }

        // Notify the region manager who originally approved the sermon
        $regionManager = $sermon->regionManager;

        if ($regionManager) {
            $this->notificationService->notify(
                $regionManager,
                'اختيار خطبة لصلاة الجمعة',
                "قام مسجد {$deliveringMosqueManager->name} باختيار الخطبة \"{$sermon->title}\" لإلقائها يوم الجمعة بتاريخ {$event->selection->friday_date}.",
                'sermon_selected_region_manager',
                [
                    'sermon_id' => (string) $sermon->id,
                    'selection_id' => (string) $event->selection->id,
                    'delivering_mosque_manager_id' => (string) $deliveringMosqueManager->id,
                    'original_mosque_manager_id' => (string) $sermon->mosque_manager_id,
                    'friday_date' => (string) $event->selection->friday_date,
                ]
            );
        }
    }
}
