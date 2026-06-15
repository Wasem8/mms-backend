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
        return ApiResponse::success($certificate, __('messages.certificate_issued'), 201);
    }

    /** Volunteer: view their own certificates */
    public function myCertificates()
    {
        $certificates = $this->service->getCertificatesForVolunteer((int) auth()->id());
        return ApiResponse::success($certificates, __('messages.certificates_retrieved'), 200);
    }

    /** Summary: total hours for a volunteer on an opportunity */
    public function totalHours(string $volunteerId, string $opportunityId)
    {
        $hours = $this->service->totalHours((int) $volunteerId, (int) $opportunityId);
        return ApiResponse::success(['total_hours' => $hours], __('messages.hours_retrieved'), 200);
    }
}
