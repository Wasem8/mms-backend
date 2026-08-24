<?php

namespace Modules\Volunteer\Services;

use App\Support\Pdf\PdfGeneratorService;
use App\Support\Storage\SupabaseStorageService;
use Modules\Volunteer\DTOs\LogHoursDTO;
use Modules\Volunteer\Events\CertificateIssued;
use Modules\Volunteer\Models\VolunteerApplication;
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
        $this->ensureAllTasksCompleted($dto->volunteerId, $dto->opportunityId);

        return DB::transaction(fn() => $this->evaluationRepo->createLog($dto));
    }

    /**
     * A manager may only log hours once every task belonging to the volunteer's
     * application on the opportunity is completed. Otherwise we reject with an error.
     */
    private function ensureAllTasksCompleted(int $volunteerId, int $opportunityId): void
    {
        $application = VolunteerApplication::where('volunteer_id', $volunteerId)
            ->where('opportunity_id', $opportunityId)
            ->with('tasks')
            ->first();

        if (! $application || ! $application->all_tasks_completed) {
            throw ValidationException::withMessages([
                'tasks' => __('messages.tasks_not_completed'),
            ]);
        }
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
        // إن كان الرابط المخزّن رابطاً كاملاً (مثل بيانات الـ seeder الوهمية
        // https://placeholder.wasl-mms.test/...) فالملف غير موجود فعلاً في الـ bucket.
        // نعيد توليد الـ PDF ورفعه لنتمكّن من تنزيله عبر رابط مُوقّع صالح.
        if (filter_var($certificate->certificate_url, FILTER_VALIDATE_URL)) {
            $certificate = $this->regenerateCertificateFile($certificate);
        }

        try {
            return $this->storage->createSignedUrl(
                $certificate->certificate_url,
                config('services.supabase.bucket')
            );
        } catch (\Throwable $e) {
            // الكائن غير موجود فعلاً في الـ bucket (NoSuchKey) أو فشل إنشاء الرابط.
            throw new \RuntimeException(__('messages.certificate_not_found'), 0, $e);
        }
    }

    /**
     * إعادة توليد ملف الـ PDF للشهادة ورفعه إلى الـ bucket، ثم تحديث المسار المخزّن.
     * تُستخدم عندما يكون المسار المخزّن رابطاً وهمياً (بيانات seeder) أو مفقوداً.
     */
    private function regenerateCertificateFile(VolunteerCertificate $certificate): VolunteerCertificate
    {
        $totalHours = $this->evaluationRepo->totalHours(
            $certificate->volunteer_id,
            $certificate->opportunity_id
        );

        $pdfContent = $this->pdfGenerator->generate(
            $this->buildCertificateHtml(
                $certificate->volunteer_id,
                $certificate->opportunity_id,
                $totalHours
            ),
            cacheKey: 'volunteer'
        );

        $bucket = config('services.supabase.bucket');
        $fileName = "volunteer_{$certificate->volunteer_id}_opportunity_{$certificate->opportunity_id}_" . now()->timestamp . '.pdf';

        $this->storage->uploadPdf($pdfContent, $fileName, $bucket);

        $certificate->certificate_url = $fileName;
        $certificate->save();

        return $certificate;
    }

    public function getCertificatesForVolunteer(int $volunteerId): Collection
    {
        $bucket = config('services.supabase.bucket');
        $certificates = $this->evaluationRepo->findCertificatesByVolunteer($volunteerId);

        return $certificates->map(function (VolunteerCertificate $certificate) use ($bucket) {
            // القيمة المخزّنة إما اسم ملف حقيقي مرفوع على Supabase، أو رابط
            // وهمي من بيانات الـ seeder (https://placeholder.wasl-mms.test/...).
            // لا نُعيد توليد ملفات الـ seeder هنا (عملية بطيئة تسبب تجاوز وقت
            // التنفيذ)، بل نُنشئ رابطاً مُوقّعاً للملفات الحقيقية فقط، ونُبقي
            // روابط الـ seeder كما هي — يمكن تنزيلها عبر نقطة التحميل الخاصة
            // بكل شهادة التي تُعيد توليدها عند الطلب.
            if (! filter_var($certificate->certificate_url, FILTER_VALIDATE_URL)) {
                try {
                    $certificate->certificate_url = $this->storage->createSignedUrl(
                        $certificate->certificate_url,
                        $bucket
                    );
                } catch (\Throwable $e) {
                    // نُبقي القيمة المخزّنة عند فشل إنشاء الرابط
                }
            }

            return $certificate;
        });
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
