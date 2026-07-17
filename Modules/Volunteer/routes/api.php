<?php

use Modules\Volunteer\Http\Controllers\VolunteerAuthController;
use Modules\Volunteer\Http\Controllers\VolunteerOpportunityController;
use Modules\Volunteer\Http\Controllers\VolunteerTaskController;
use Modules\Volunteer\Http\Controllers\VolunteerEvaluationController;

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

        // Task assignment
        Route::post('volunteer/tasks',[VolunteerTaskController::class, 'store']);

        // Hours & evaluation
        Route::post('volunteer/logs',                          [VolunteerEvaluationController::class, 'logHours']);
        Route::post('volunteer/certificates/{volunteerId}/{opportunityId}', [VolunteerEvaluationController::class, 'issueCertificate']);
        Route::get('volunteer/hours/{volunteerId}/{opportunityId}',        [VolunteerEvaluationController::class, 'totalHours']);
    });

    // ─── Volunteer routes ───────────────────────────────────────────────────────

    Route::middleware('role:volunteer')->group(function () {
        // Browse open opportunities
        Route::get('volunteer/opportunities',                  [VolunteerOpportunityController::class, 'index']);
        Route::get('volunteer/opportunities/{id}',             [VolunteerOpportunityController::class, 'show']);

        // Apply & track own applications
        Route::post('volunteer/opportunities/{opportunityId}/apply', [VolunteerOpportunityController::class, 'apply']);
        Route::get('volunteer/my-applications',                     [VolunteerOpportunityController::class, 'myApplications']);

        // Tasks
        Route::get('volunteer/applications/{applicationId}/tasks',  [VolunteerTaskController::class, 'index']);
        Route::post('volunteer/tasks/{taskId}/complete',             [VolunteerTaskController::class, 'complete']);

        // Personal logs & certificates
        Route::get('volunteer/my-logs',         [VolunteerEvaluationController::class, 'myLogs']);
        Route::get('volunteer/my-certificates', [VolunteerEvaluationController::class, 'myCertificates']);
    });
});
