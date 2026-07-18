<?php

namespace Modules\Volunteer\Services;

use Modules\Volunteer\DTOs\LogHoursDTO;
use Modules\Volunteer\Events\CertificateIssued;
use Modules\Volunteer\Models\VolunteerCertificate;
use Modules\Volunteer\Models\VolunteerLog;
use Modules\Volunteer\Models\VolunteerOpportunity;
use Modules\Volunteer\Repositories\Contracts\VolunteerEvaluationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Modules\User\Models\User;

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
            $url = $this->generateLocalPdf($volunteerId, $opportunityId, $totalHours);

            $certificate = $this->evaluationRepo->issueCertificate($volunteerId, $opportunityId, $url);

            event(new CertificateIssued($certificate));

            dispatch(function () use ($volunteerId, $opportunityId, $totalHours) {
                $this->uploadPdfAsync($volunteerId, $opportunityId, $totalHours);
            })->afterResponse();

            return $certificate;
        });
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
        return \Modules\Volunteer\Models\VolunteerCertificate::find($certificateId);
    }

    public function buildCertificatePdfContent(int $volunteerId, int $opportunityId): string
    {
        return $this->renderCertificateMpdf($volunteerId, $opportunityId);
    }

    private function getCertificateViewData(int $volunteerId, int $opportunityId): array
    {
        $volunteer = User::find($volunteerId);
        $opportunity = VolunteerOpportunity::with('mosque')->find($opportunityId);
        $totalHours = $this->evaluationRepo->totalHours($volunteerId, $opportunityId);

        if (!$volunteer || !$opportunity) {
            throw new \RuntimeException(__('messages.volunteer_not_found'));
        }

        return [
            'volunteerName'    => $volunteer->name,
            'opportunityTitle' => $opportunity->title,
            'mosqueName'       => $opportunity->mosque?->name ?? '—',
            'totalHours'       => $totalHours,
            'issuedAt'         => now()->format('Y/m/d'),
        ];
    }

    private function renderCertificateMpdf(int $volunteerId, int $opportunityId): string
    {
        $data = $this->getCertificateViewData($volunteerId, $opportunityId);

        $html = view('volunteer::certificate', $data)->render();

        $tempDir = storage_path('app/mpdf_cache');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $localRegular = storage_path('app/fonts/Cairo-Regular.ttf');
        $localBold = storage_path('app/fonts/Cairo-Bold.ttf');

        if (!file_exists($localRegular)) {
            $remoteRegular = 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Regular.ttf';
            file_put_contents($localRegular, file_get_contents($remoteRegular));
        }
        if (!file_exists($localBold)) {
            $remoteBold = 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Bold.ttf';
            file_put_contents($localBold, file_get_contents($remoteBold));
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 8,
            'tempDir'       => $tempDir,
            'fontDir'       => [storage_path('app/fonts')],
            'fontdata'      => [
                'cairo' => [
                    'R'      => 'Cairo-Regular.ttf',
                    'B'      => 'Cairo-Bold.ttf',
                    'useOTL' => 0xFF,
                ]
            ],
            'default_font' => 'cairo',
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }

    private function generateLocalPdf(int $volunteerId, int $opportunityId, float $totalHours): string
    {
        $pdfContent = $this->renderCertificateMpdf($volunteerId, $opportunityId);

        $pdfDir = storage_path('app/certificates');
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }

        $fileName = "volunteer_{$volunteerId}_opportunity_{$opportunityId}.pdf";
        $filePath = $pdfDir . '/' . $fileName;

        file_put_contents($filePath, $pdfContent);

        return $filePath;
    }

    private function uploadPdfAsync(int $volunteerId, int $opportunityId, float $totalHours): void
    {
        try {
            $pdfContent = $this->renderCertificateMpdf($volunteerId, $opportunityId);
            $fileName = "certificates/volunteer_{$volunteerId}_opportunity_{$opportunityId}.pdf";

            $baseUrl = config('services.supabase.url');
            $bucket  = config('services.supabase.bucket');
            $key     = config('services.supabase.key');

            $uploadUrl = $baseUrl . '/storage/v1/object/' . $bucket . '/' . $fileName;

            Http::timeout(10)
                ->withHeaders([
                    'apikey'       => $key,
                    'Authorization'=> 'Bearer ' . $key,
                    'Content-Type' => 'application/pdf',
                ])->withBody($pdfContent, 'application/pdf')
                ->post($uploadUrl);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Certificate upload to Supabase failed: ' . $e->getMessage());
        }
    }
}
