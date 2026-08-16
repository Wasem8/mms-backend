<?php

use Modules\Volunteer\Http\Controllers\VolunteerAuthController;
use Modules\Volunteer\Http\Controllers\VolunteerOpportunityController;
use Modules\Volunteer\Http\Controllers\VolunteerTaskController;
use Modules\Volunteer\Http\Controllers\VolunteerEvaluationController;
use Modules\Volunteer\Http\Controllers\VolunteerController;

use Illuminate\Support\Facades\Route;

// ─── Volunteer Auth (public) ──────────────────────────────────────────────────
Route::post('volunteer/register', [VolunteerAuthController::class, 'register']);
Route::post('volunteer/login', [VolunteerAuthController::class, 'login']);

/*
| Volunteer Management Module Routes
|--------------------------------------------------------------------------
|
| Grouped under /api/v1/ with Sanctum auth.
| Role middleware assumes your project uses a role system (e.g. spatie/laravel-permission).
| Replace 'role:manager' / 'role:volunteer' with your actual middleware.
|
*/

Route::middleware('auth:api')->group(function () {

    // ─── Opportunities ─────────────────────────────────────────────────────────

    // Manager routes
    Route::middleware('role:mosque_manager')->group(function () {
        Route::get('volunteer/manager/opportunities',          [VolunteerOpportunityController::class, 'managerIndex']);
        Route::post('volunteer/opportunities',                  [VolunteerOpportunityController::class, 'store']);
        Route::put('volunteer/opportunities/{id}',            [VolunteerOpportunityController::class, 'update']);
        Route::post('volunteer/opportunities/{id}/close',      [VolunteerOpportunityController::class, 'close']);

        // Applications management
        Route::get('volunteer/opportunities/{opportunityId}/applications',          [VolunteerOpportunityController::class, 'applications']);
        Route::post('volunteer/applications/{applicationId}/approve',                [VolunteerOpportunityController::class, 'approveApplication']);
        Route::post('volunteer/applications/{applicationId}/reject',                 [VolunteerOpportunityController::class, 'rejectApplication']);

        // Tasks: create general tasks on an opportunity, view them all, then distribute to approved volunteers
        Route::post('volunteer/opportunities/{opportunityId}/tasks', [VolunteerTaskController::class, 'store']);
        Route::get('volunteer/opportunities/{opportunityId}/tasks',  [VolunteerTaskController::class, 'index']);
        Route::post('volunteer/tasks/{taskId}/assign',                [VolunteerTaskController::class, 'assign']);

        // Hours & evaluation
        Route::post('volunteer/logs',                          [VolunteerEvaluationController::class, 'logHours']);
        Route::post('volunteer/certificates/{volunteerId}/{opportunityId}', [VolunteerEvaluationController::class, 'issueCertificate']);
        Route::get('volunteer/certificates/{volunteerId}/{opportunityId}/download', [VolunteerEvaluationController::class, 'downloadCertificate']);
        Route::get('volunteer/certificates/{volunteerId}/{opportunityId}/stream',  [VolunteerEvaluationController::class, 'streamCertificate']);
        Route::get('volunteer/hours/{volunteerId}/{opportunityId}',        [VolunteerEvaluationController::class, 'totalHours']);
    });

    // ─── Volunteer routes ───────────────────────────────────────────────────────

    Route::middleware('role:volunteer')->group(function () {
        // Browse open opportunities
        Route::get('volunteer/opportunities',                  [VolunteerOpportunityController::class, 'index']);

        // Apply & track own applications
        Route::post('volunteer/opportunities/{opportunityId}/apply', [VolunteerOpportunityController::class, 'apply']);
        Route::get('volunteer/my-applications',                     [VolunteerOpportunityController::class, 'myApplications']);

        // Tasks: view tasks assigned to my own approved application, mark my own task as done
        Route::get('volunteer/applications/{applicationId}/tasks',  [VolunteerTaskController::class, 'applicationTasks']);
        Route::post('volunteer/tasks/{taskId}/complete',             [VolunteerTaskController::class, 'complete']);

        // Personal logs & certificates
        Route::get('volunteer/my-logs',         [VolunteerEvaluationController::class, 'myLogs']);
        Route::get('volunteer/my-certificates', [VolunteerEvaluationController::class, 'myCertificates']);
        Route::get('volunteer/my-certificates/{certificateId}/download', [VolunteerEvaluationController::class, 'myCertificateDownload']);
    });
    Route::get('volunteer/opportunities/{id}',             [VolunteerOpportunityController::class, 'show'])->middleware('role:volunteer,mosque_manager');

    // List all volunteers (super_admin: all; mosque_manager: scoped to their mosque)
    Route::get('volunteer/volunteers', [VolunteerController::class, 'index'])
        ->middleware('role:super_admin,mosque_manager');

    // Mosque-manager dashboard stats cards (scoped to their mosque)
    Route::get('volunteer/stats', [VolunteerController::class, 'stats'])
        ->middleware('role:mosque_manager');
});
