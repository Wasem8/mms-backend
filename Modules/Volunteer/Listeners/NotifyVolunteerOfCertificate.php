<?php

namespace Modules\Volunteer\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Volunteer\Events\CertificateIssued;

class NotifyVolunteerOfCertificate
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(CertificateIssued $event): void
    {
        $certificate = $event->certificate;
        $volunteer   = $certificate->volunteer;

        if (!$volunteer) {
            return;
        }

        $this->notificationService->notify(
            $volunteer,
            'تم إصدار شهادتك',
            'تم إصدار شهادة التطوع الخاصة بفرصة «' . $certificate->opportunity->title . '».',
            'certificate_issued',
            [
                'certificate_id' => (string) $certificate->id,
                'opportunity_id' => (string) $certificate->opportunity_id,
            ]
        );
    }
}
