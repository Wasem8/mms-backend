<?php

namespace Modules\Volunteer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Volunteer\Models\VolunteerOpportunity;
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

    /** Manager: list opportunities for their own mosque, with optional search + status filter */
    public function managerIndex(Request $request)
    {
        $mosque = auth()->user()->managedMosque;

        if (! $mosque) {
            return ApiResponse::error(__('messages.no_mosque_assigned_to_manager'), 422);
        }

        $perPage = $request->integer('per_page', 15);
        $search  = $request->input('search');
        $status  = $request->input('status');

        $opportunities = $this->service->listForManager(
            (int) $mosque->id,
            $perPage,
            $search,
            $status
        );

        return ApiResponse::success(
            $opportunities->items(),
            __('messages.opportunities_retrieved'),
            $opportunities
        );
    }

    /** Volunteer: list open opportunities for their own mosque */
    public function index()
    {
        $mosqueId = (int) auth()->user()->mosque_id;

        $Opportunities = $this->service->listOpen($mosqueId);
        return ApiResponse::success($Opportunities, __('messages.opportunities_retrieved'), 200);
    }

    /** Manager: create an opportunity for the mosque they manage (see CreateOpportunityRequest::toDTO) */
    public function store(RequestsCreateOpportunityRequest $request)
    {
        $opportunity = $this->service->create($request->toDTO());
        return ApiResponse::success($opportunity, __('messages.opportunity_created'), 201);
    }

    public function show(string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        $this->ensureManagerOwnsOpportunity($opportunity);
        $opportunity->load('acceptedApplications');
        return ApiResponse::success($opportunity, __('messages.opportunity_retrieved'), 200);
    }

    public function update(UpdateOpportunityRequest $request, string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        $this->ensureManagerOwnsOpportunity($opportunity);
        $updated     = $this->service->update($opportunity, $request->toDTO());
        return ApiResponse::success($updated, __('messages.opportunity_updated'), 200);
    }

    public function close(string $id)
    {
        $opportunity = $this->service->findOrFail((int) $id);
        $this->ensureManagerOwnsOpportunity($opportunity);
        $closed      = $this->service->close($opportunity);
        return ApiResponse::success($closed, __('messages.opportunity_closed'), 200);
    }

    /**
     * A mosque_manager may only access opportunities of the mosque they manage.
     * Volunteers (and other roles) are not restricted here.
     */
    private function ensureManagerOwnsOpportunity(VolunteerOpportunity $opportunity): void
    {
        $user = auth()->user();

        if (! $user || ! $user->isMosqueManager()) {
            return;
        }

        $mosque = $user->managedMosque;

        if (! $mosque || $opportunity->mosque_id !== $mosque->id) {
            abort(403, __('messages.unauthorized_opportunity_access'));
        }
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
