<?php

namespace Modules\Volunteer\Services;

use Modules\Volunteer\DTOs\LogHoursDTO;
use Modules\Volunteer\Events\CertificateIssued;
use Modules\Volunteer\Models\VolunteerCertificate;
use Modules\Volunteer\Models\VolunteerLog;
use Modules\Volunteer\Repositories\Contracts\VolunteerEvaluationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class VolunteerEvaluationService
{
    public function __construct(
        private readonly VolunteerEvaluationRepositoryInterface $evaluationRepo,
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

        return DB::transaction(function () use ($volunteerId, $opportunityId, $totalHours): VolunteerCertificate {
            $url = $this->generateCertificatePdf($volunteerId, $opportunityId, $totalHours);

            $certificate = $this->evaluationRepo->issueCertificate($volunteerId, $opportunityId, $url);

            event(new CertificateIssued($certificate));

            return $certificate;
        });
    }

    public function getCertificatesForVolunteer(int $volunteerId): Collection
    {
        return $this->evaluationRepo->findCertificatesByVolunteer($volunteerId);
    }

    private function generateCertificatePdf(int $volunteerId, int $opportunityId, float $totalHours): string
    {
        $volunteer = \Modules\User\Models\User::find($volunteerId);
        $opportunity = \Modules\Volunteer\Models\VolunteerOpportunity::with('mosque')->find($opportunityId);

        if (!$volunteer || !$opportunity) {
            throw new \RuntimeException(__('messages.volunteer_not_found'));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('volunteer::certificate', [
            'volunteerName'    => $volunteer->name,
            'opportunityTitle' => $opportunity->title,
            'mosqueName'       => $opportunity->mosque?->name ?? '—',
            'totalHours'       => $totalHours,
            'issuedAt'         => now()->format('Y/m/d'),
        ]);

        $pdfContent = $pdf->output();

        $fileName = "certificates/volunteer_{$volunteerId}_opportunity_{$opportunityId}.pdf";

        $this->uploadPdfToSupabase($pdfContent, $fileName);

        return $this->createPublicUrl($fileName);
    }

    private function uploadPdfToSupabase(string $pdfContent, string $fileName): void
    {
        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.bucket');
        $key     = config('services.supabase.key');

        $uploadUrl = $baseUrl . '/storage/v1/object/' . $bucket . '/' . $fileName;

        $response = Http::retry(3, 1000)
            ->timeout(60)
            ->withHeaders([
                'apikey'       => $key,
                'Authorization'=> 'Bearer ' . $key,
                'Content-Type' => 'application/pdf',
            ])->withBody($pdfContent, 'application/pdf')
            ->post($uploadUrl);

        if (!$response->successful()) {
            throw new \RuntimeException(__('messages.upload_failed', ['error' => $response->body()]));
        }
    }

    private function createPublicUrl(string $fileName): string
    {
        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.bucket');

        return $baseUrl . '/storage/v1/object/public/' . $bucket . '/' . $fileName;
    }
}
