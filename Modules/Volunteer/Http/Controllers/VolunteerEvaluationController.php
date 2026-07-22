<?php

namespace Modules\Volunteer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Volunteer\DTOs\LogHoursDTO;
use Modules\Volunteer\Http\Requests\LogHoursRequest;
use Modules\Volunteer\Services\VolunteerEvaluationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class VolunteerEvaluationController extends Controller
{
    public function __construct(
        private readonly VolunteerEvaluationService $service,
    ) {}

    /** Manager: log hours and evaluation for a volunteer */
    public function logHours(LogHoursRequest $request)
    {
        $log = $this->service->logHours($request->toDTO());
        return ApiResponse::success($log, __('messages.hours_logged'), 201);
    }

    /** Volunteer: view their own logs */
    public function myLogs()
    {
        $logs = $this->service->getLogsForVolunteer((int) auth()->id());
        return ApiResponse::success($logs, __('messages.logs_retrieved'), 200);
    }

    /** Manager: issue a certificate */
    public function issueCertificate(string $volunteerId, string $opportunityId)
    {
        $certificate = $this->service->issueCertificate((int) $volunteerId, (int) $opportunityId);
        $downloadUrl = $this->service->getCertificateDownloadUrl($certificate);

        return ApiResponse::success(
            array_merge($certificate->toArray(), ['certificate_url' => $downloadUrl]),
            __('messages.certificate_issued'),
            201
        );
    }

    /** Manager: download certificate as PDF */
    public function downloadCertificate(string $volunteerId, string $opportunityId)
    {
        $certificate = $this->service->findCertificate((int) $volunteerId, (int) $opportunityId);

        if (!$certificate) {
            return ApiResponse::error(__('messages.certificate_not_found'), 404);
        }

        $url = $this->service->getCertificateDownloadUrl($certificate);

        return redirect()->away($url);
    }

    /** Manager: stream certificate as PDF in browser */
    public function streamCertificate(string $volunteerId, string $opportunityId)
    {
        $certificate = $this->service->findCertificate((int) $volunteerId, (int) $opportunityId);

        if (!$certificate) {
            return ApiResponse::error(__('messages.certificate_not_found'), 404);
        }

        $url = $this->service->getCertificateDownloadUrl($certificate);

        return redirect()->away($url);
    }

    public function myCertificateDownload(string $certificateId)
    {
        $certificate = $this->service->findCertificateById((int) $certificateId);

        if (!$certificate || $certificate->volunteer_id !== (int) auth()->id()) {
            return ApiResponse::error(__('messages.certificate_not_found'), 404);
        }

        $url = $this->service->getCertificateDownloadUrl($certificate);

        return redirect()->away($url);
    }
    /** Summary: total hours for a volunteer on an opportunity */
    public function totalHours(string $volunteerId, string $opportunityId)
    {
        $hours = $this->service->totalHours((int) $volunteerId, (int) $opportunityId);
        return ApiResponse::success(['total_hours' => $hours], __('messages.hours_retrieved'), 200);
    }
    public function myCertificates()
    {
        $certificates = $this->service->getCertificatesForVolunteer((int) auth()->id());
        return ApiResponse::success($certificates, __('messages.certificates_retrieved'), 200);
    }
}
