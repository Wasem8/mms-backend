<?php

namespace Modules\Community\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Community\Events\DawahProgramCreated;
use Modules\User\Models\User;

class SendDawahProgramCreatedNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(DawahProgramCreated $event): void
    {
        $program = $event->program;

        $recipients = User::where('mosque_id', $program->mosque_id)
            ->where('id', '!=', $event->creatorId) // don't notify the manager who created it
            ->get();

        foreach ($recipients as $recipient) {
            $this->notificationService->notify(
                $recipient,
                'برنامج دعوي جديد',
                "تم إضافة برنامج جديد \"{$program->program_name}\" في مسجدك.",
                'dawah_program_created',
                [
                    'program_id' => (string) $program->id,
                    'mosque_id'  => (string) $program->mosque_id,
                ]
            );
        }
    }
}
