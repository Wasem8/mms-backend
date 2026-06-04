<?php

namespace Modules\Volunteer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Volunteer\Resources\VolunteerApplicationResource;
use Modules\Volunteer\Resources\VolunteerOpportunityResource;
use Modules\Volunteer\Services\VolunteerOpportunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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

        $mosqueId = (int) auth()->user()->mosque_id;

        $Opportunities = $this->service->listForManager($mosqueId);
        return ApiResponse::success($Opportunities,'Volunteer opportunities retrieved successfully.',200);
    }

    /** Volunteer: list open opportunities */
    public function index()
    {
        $mosqueId = (int) auth()->user()->mosque_id;
        $Opportunities = $this->service->listOpen($mosqueId);
        return ApiResponse::success($Opportunities,'Open volunteer opportunities retrieved successfully.',200);
    }

    public function store(RequestsCreateOpportunityRequest $request)
    {
        $opportunity = $this->service->create($request->toDTO());
        return ApiResponse::success($opportunity, 'Volunteer opportunity created successfully.', 201);
    }

    public function show(string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        return ApiResponse::success($opportunity, 'Volunteer opportunity retrieved successfully.', 200);
    }

    public function update(UpdateOpportunityRequest $request, string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        $updated     = $this->service->update($opportunity, $request->toDTO());
        return ApiResponse::success($updated, 'Volunteer opportunity updated successfully.', 200);
    }

    public function close(string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        $closed      = $this->service->close($opportunity);
        return ApiResponse::success($closed, 'Volunteer opportunity closed successfully.', 200);
    }

    /** Manager: list applications for an opportunity */
    public function applications(string $opportunityId)
    {
        $applications = $this->service->listApplications((int) $opportunityId);
        return ApiResponse::success($applications, 'Applications retrieved successfully.', 200);
    }

    /** Volunteer: apply for an opportunity */
    public function apply(string $opportunityId)
    {
        $application = $this->service->apply((int) $opportunityId, (int) auth()->id());
        return ApiResponse::success($application, 'Application submitted successfully.', 201);
    }

    /** Volunteer: my own applications */
    public function myApplications()
    {
        $applications = $this->service->listMyApplications((int) auth()->id());
        return ApiResponse::success($applications, 'My applications retrieved successfully.', 200);
    }

    /** Manager: approve an application */
    public function approveApplication(string $applicationId)
    {
        $application = $this->service->approveApplication((int) $applicationId);
        return ApiResponse::success($application, 'Application approved successfully.', 200);
    }

    /** Manager: reject an application */
    public function rejectApplication(string $applicationId)
    {
        $application = $this->service->rejectApplication((int) $applicationId);
        return ApiResponse::success($application, 'Application rejected successfully.', 200);
    }
}
