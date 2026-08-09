<?php

namespace Modules\Volunteer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Volunteer\Services\VolunteerOpportunityService;
use Modules\Volunteer\Http\Requests\CreateOpportunityRequest as RequestsCreateOpportunityRequest;
use Modules\Volunteer\Http\Requests\UpdateOpportunityRequest;
use Modules\Volunteer\Http\Requests\AssignTaskRequest;
use Modules\Volunteer\Http\Requests\LogHoursRequest;

class VolunteerOpportunityController extends Controller
{
    public function __construct(
        private readonly VolunteerOpportunityService $service,
    ) {}

    /** Manager: list all opportunities for their mosque */
    public function managerIndex()
    {
        $mosque = auth()->user()->managedMosque;

        if (! $mosque) {
            return ApiResponse::error(__('messages.no_mosque_assigned_to_manager'), 422);
        }

        $Opportunities = $this->service->listForManager((int) $mosque->id);
        return ApiResponse::success($Opportunities, __('messages.opportunities_retrieved'), 200);
    }

    /** Volunteer: list open opportunities */
    public function index()
    {
        $mosque = auth()->user()->managedMosque;

        if (! $mosque) {
            return ApiResponse::error(__('messages.no_mosque_assigned_to_manager'), 422);
        }

        $Opportunities = $this->service->listOpen((int) $mosque->id);
        return ApiResponse::success($Opportunities, __('messages.opportunities_retrieved'), 200);
    }

    public function store(RequestsCreateOpportunityRequest $request)
    {
        $opportunity = $this->service->create($request->toDTO());
        return ApiResponse::success($opportunity, __('messages.opportunity_created'), 201);
    }

    public function show(string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        return ApiResponse::success($opportunity, __('messages.opportunity_retrieved'), 200);
    }

    public function update(UpdateOpportunityRequest $request, string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        $updated     = $this->service->update($opportunity, $request->toDTO());
        return ApiResponse::success($updated, __('messages.opportunity_updated'), 200);
    }

    public function close(string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        $closed      = $this->service->close($opportunity);
        return ApiResponse::success($closed, __('messages.opportunity_closed'), 200);
    }

    /** Manager: list applications for an opportunity */
    public function applications(string $opportunityId)
    {
        $applications = $this->service->listApplications((int) $opportunityId);
        return ApiResponse::success($applications, __('messages.applications_retrieved'), 200);
    }

    /** Volunteer: apply for an opportunity */
    public function apply(string $opportunityId)
    {
        $application = $this->service->apply((int) $opportunityId, (int) auth()->id());
        return ApiResponse::success($application, __('messages.application_submitted'), 201);
    }

    /** Volunteer: my own applications */
    public function myApplications()
    {
        $applications = $this->service->listMyApplications((int) auth()->id());
        return ApiResponse::success($applications, __('messages.my_applications_retrieved'), 200);
    }

    /** Manager: approve an application */
    public function approveApplication(string $applicationId)
    {
        $application = $this->service->approveApplication((int) $applicationId);
        return ApiResponse::success($application, __('messages.application_approved'), 200);
    }

    /** Manager: reject an application */
    public function rejectApplication(string $applicationId)
    {
        $application = $this->service->rejectApplication((int) $applicationId);
        return ApiResponse::success($application, __('messages.application_rejected'), 200);
    }
}
