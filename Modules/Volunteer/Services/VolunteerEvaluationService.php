<?php

namespace Modules\Volunteer\Services;

use App\Support\Pdf\PdfGeneratorService;
use App\Support\Storage\SupabaseStorageService;
use Modules\Volunteer\DTOs\LogHoursDTO;
use Modules\Volunteer\Events\CertificateIssued;
use Modules\Volunteer\Models\VolunteerCertificate;
use Modules\Volunteer\Models\VolunteerLog;
use Modules\Volunteer\Models\VolunteerOpportunity;
use Modules\Volunteer\Repositories\Contracts\VolunteerEvaluationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\User\Models\User;

class VolunteerEvaluationService
{
    public function __construct(
        private readonly VolunteerEvaluationRepositoryInterface $evaluationRepo,
        private readonly PdfGeneratorService $pdfGenerator,
        private readonly SupabaseStorageService $storage,
    ) {}

    public function logHours(LogHoursDTO $dto): VolunteerLog
    {
        return DB::transaction(fn() => $this->evaluationRepo->createLog($dto));
    }

    public function getLogsForVolunteer(int $volunteerId): Collection
    {
        return $this->evaluationRepo->findLogsByVolunteer($volunteerId);
    }

    public function totalHours(int $volunteerId, int $opportunityId): float
    {
        return $this->evaluationRepo->totalHours($volunteerId, $opportunityId);
    }

    public function issueCertificate(int $volunteerId, int $opportunityId): VolunteerCertificate
    {
        $existing = $this->evaluationRepo->findCertificate($volunteerId, $opportunityId);

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'certificate' => __('messages.certificate_already_exists'),
            ]);
        }

        $totalHours = $this->evaluationRepo->totalHours($volunteerId, $opportunityId);

        if ($totalHours <= 0) {
            throw ValidationException::withMessages([
                'hours' => __('messages.no_hours_for_certificate'),
            ]);
        }

        // توليد ورفع الـ PDF بره الـ DB transaction — دي عمليات I/O بطيئة
        // (network + mpdf rendering)، مينفعش نمسك transaction مفتوحة عليها
        $pdfContent = $this->pdfGenerator->generate(
            $this->buildCertificateHtml($volunteerId, $opportunityId, $totalHours),
            cacheKey: 'volunteer'
        );

        $bucket = config('services.supabase.bucket');
        $fileName = "volunteer_{$volunteerId}_opportunity_{$opportunityId}_" . now()->timestamp . '.pdf';

        $this->storage->uploadPdf($pdfContent, $fileName, $bucket);

        return DB::transaction(function () use ($volunteerId, $opportunityId, $fileName): VolunteerCertificate {
            $certificate = $this->evaluationRepo->issueCertificate($volunteerId, $opportunityId, $fileName);
            event(new CertificateIssued($certificate));
            return $certificate;
        });
    }
    public function getCertificateDownloadUrl(VolunteerCertificate $certificate): string
{
    return $this->storage->createSignedUrl(
        $certificate->certificate_url, // now holds the file path, e.g. "volunteer_12_opportunity_1_...pdf"
        config('services.supabase.bucket')
    );
}

    public function getCertificatesForVolunteer(int $volunteerId): Collection
    {
        return $this->evaluationRepo->findCertificatesByVolunteer($volunteerId);
    }

    public function findCertificate(int $volunteerId, int $opportunityId): ?VolunteerCertificate
    {
        return $this->evaluationRepo->findCertificate($volunteerId, $opportunityId);
    }

    public function findCertificateById(int $certificateId): ?VolunteerCertificate
    {
        return VolunteerCertificate::find($certificateId);
    }

    private function buildCertificateHtml(int $volunteerId, int $opportunityId, float $totalHours): string
    {
        $volunteer   = User::find($volunteerId);
        $opportunity = VolunteerOpportunity::with('mosque')->find($opportunityId);

        if (!$volunteer || !$opportunity) {
            throw new \RuntimeException(__('messages.volunteer_not_found'));
        }

        return view('volunteer::certificate', [
            'volunteerName'    => $volunteer->name,
            'opportunityTitle' => $opportunity->title,
            'mosqueName'       => $opportunity->mosque?->name ?? '—',
            'totalHours'       => $totalHours,
            'issuedAt'         => now()->format('Y/m/d'),
        ])->render();
    }
}
