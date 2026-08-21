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
| IMPORTANT: We do NOT nest routes inside an outer Route::middleware('role:...')
| group anymore. Middleware STACKS rather than overrides, so a route inside
| Route::middleware('role:mosque_manager')->group(...) that also declares
| ->middleware('role:mosque_manager,super_admin') inline ends up requiring
| BOTH checks to pass — meaning a pure super_admin (without mosque_manager)
| gets rejected by the outer group before the inline check is ever reached.
| Instead, every route below declares its full allowed-role list directly.
|
*/

Route::middleware('auth:api')->group(function () {

    // ─── Opportunities ─────────────────────────────────────────────────────────

    // Manager routes (mosque_manager + super_admin)
    Route::get('volunteer/manager/opportunities',           [VolunteerOpportunityController::class, 'managerIndex'])->middleware('role:mosque_manager,super_admin');
    Route::post('volunteer/opportunities',                  [VolunteerOpportunityController::class, 'store'])->middleware('role:mosque_manager,super_admin');
    Route::put('volunteer/opportunities/{id}',              [VolunteerOpportunityController::class, 'update'])->middleware('role:mosque_manager,super_admin');
    Route::post('volunteer/opportunities/{id}/close',       [VolunteerOpportunityController::class, 'close'])->middleware('role:mosque_manager,super_admin');

    // Applications management
    Route::get('volunteer/opportunities/{opportunityId}/applications',  [VolunteerOpportunityController::class, 'applications'])->middleware('role:mosque_manager,super_admin');
    Route::post('volunteer/applications/{applicationId}/approve',       [VolunteerOpportunityController::class, 'approveApplication'])->middleware('role:mosque_manager,super_admin');
    Route::post('volunteer/applications/{applicationId}/reject',        [VolunteerOpportunityController::class, 'rejectApplication'])->middleware('role:mosque_manager,super_admin');

    // Tasks: create general tasks on an opportunity, view them all, then distribute to approved volunteers
    Route::post('volunteer/opportunities/{opportunityId}/tasks', [VolunteerTaskController::class, 'store'])->middleware('role:mosque_manager,super_admin');
    Route::get('volunteer/opportunities/{opportunityId}/tasks',  [VolunteerTaskController::class, 'index'])->middleware('role:mosque_manager,super_admin');
    Route::post('volunteer/tasks/{taskId}/assign',                [VolunteerTaskController::class, 'assign'])->middleware('role:mosque_manager,super_admin');

    // Hours & evaluation
    Route::post('volunteer/logs',                          [VolunteerEvaluationController::class, 'logHours'])->middleware('role:mosque_manager,super_admin');
    Route::post('volunteer/certificates/{volunteerId}/{opportunityId}', [VolunteerEvaluationController::class, 'issueCertificate'])->middleware('role:mosque_manager,super_admin');
    Route::get('volunteer/certificates/{volunteerId}/{opportunityId}/download', [VolunteerEvaluationController::class, 'downloadCertificate'])->middleware('role:mosque_manager,super_admin');
    Route::get('volunteer/certificates/{volunteerId}/{opportunityId}/stream',  [VolunteerEvaluationController::class, 'streamCertificate'])->middleware('role:mosque_manager,super_admin');
    Route::get('volunteer/hours/{volunteerId}/{opportunityId}',        [VolunteerEvaluationController::class, 'totalHours'])->middleware('role:mosque_manager,super_admin');

    // ─── Volunteer routes ───────────────────────────────────────────────────────

    // Browse open opportunities
    Route::get('volunteer/opportunities',                  [VolunteerOpportunityController::class, 'index'])->middleware('role:volunteer,super_admin');

    // Apply & track own applications
    Route::post('volunteer/opportunities/{opportunityId}/apply', [VolunteerOpportunityController::class, 'apply'])->middleware('role:volunteer,super_admin');
    Route::get('volunteer/my-applications',                     [VolunteerOpportunityController::class, 'myApplications'])->middleware('role:volunteer,super_admin');

    // Tasks: view tasks assigned to my own approved application, mark my own task as done
    Route::get('volunteer/applications/{applicationId}/tasks',  [VolunteerTaskController::class, 'applicationTasks'])->middleware('role:volunteer,super_admin');
    Route::post('volunteer/tasks/{taskId}/complete',             [VolunteerTaskController::class, 'complete'])->middleware('role:volunteer,super_admin');

    // Personal logs & certificates
    Route::get('volunteer/my-logs',         [VolunteerEvaluationController::class, 'myLogs'])->middleware('role:volunteer,super_admin');
    Route::get('volunteer/my-certificates', [VolunteerEvaluationController::class, 'myCertificates'])->middleware('role:volunteer,super_admin');
    Route::get('volunteer/my-certificates/{certificateId}/download', [VolunteerEvaluationController::class, 'myCertificateDownload'])->middleware('role:volunteer,super_admin');

    Route::get('volunteer/opportunities/{id}', [VolunteerOpportunityController::class, 'show'])->middleware('role:volunteer,mosque_manager,super_admin');

    // List all volunteers (super_admin: all; mosque_manager: scoped to their mosque)
    Route::get('volunteer/volunteers', [VolunteerController::class, 'index'])
        ->middleware('role:super_admin,mosque_manager');

    // Mosque-manager dashboard stats cards (scoped to their mosque)
    Route::get('volunteer/stats', [VolunteerController::class, 'stats'])
        ->middleware('role:mosque_manager,super_admin');
});
