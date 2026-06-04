<?php

namespace Modules\Volunteer\Services;

use Modules\Volunteer\DTOs\LogHoursDTO;
use Modules\Volunteer\Events\CertificateIssued;
use Modules\Volunteer\Models\VolunteerCertificate;
use Modules\Volunteer\Models\VolunteerLog;
use Modules\Volunteer\Repositories\Contracts\VolunteerEvaluationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                'certificate' => 'A certificate has already been issued for this volunteer and opportunity.',
            ]);
        }

        $totalHours = $this->evaluationRepo->totalHours($volunteerId, $opportunityId);

        if ($totalHours <= 0) {
            throw ValidationException::withMessages([
                'hours' => 'No logged hours found. Cannot issue a certificate.',
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

    /**
     * Generate a PDF certificate and store it. Returns the public URL.
     * Replace the body of this method with a real PDF library (e.g. Barryvdh\LaravelDompdf).
     */
    private function generateCertificatePdf(int $volunteerId, int $opportunityId, float $totalHours): string
    {
        // Example stub — swap for DomPDF / Snappy in production:
        //
        // $pdf = Pdf::loadView('volunteer::certificate', [
        //     'volunteer_id'   => $volunteerId,
        //     'opportunity_id' => $opportunityId,
        //     'total_hours'    => $totalHours,
        //     'issued_at'      => now()->format('Y-m-d'),
        // ]);
        //
        // $filename = "certificates/volunteer_{$volunteerId}_opportunity_{$opportunityId}.pdf";
        // Storage::disk('s3')->put($filename, $pdf->output());
        // return Storage::disk('s3')->url($filename);

        $filename = "certificates/volunteer_{$volunteerId}_opportunity_{$opportunityId}.pdf";
        return Storage::url($filename);
    }
}
