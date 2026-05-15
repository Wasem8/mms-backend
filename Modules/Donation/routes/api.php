<?php

use Illuminate\Support\Facades\Route;
use Modules\Donation\Http\Controllers\CampaignController;
use Modules\Donation\Http\Controllers\DonationController;

Route::get('donations', [DonationController::class, 'index'])->name('donation.index');
Route::get('donations/{id}', [DonationController::class, 'show']);
Route::post('donations', [DonationController::class, 'store'])->name('donation.store');

Route::middleware(['auth:api'])->group(function () {
    Route::put('donations/{donation}', [DonationController::class, 'update']);
    Route::delete('donations/{donation}', [DonationController::class, 'destroy'])->name('donation.destroy');
});



Route::prefix('mosques/{mosqueId}/campaigns')->group(function () {
    Route::get('/',       [CampaignController::class, 'index'])->name('campaign.index');
    Route::get('/stats',  [CampaignController::class, 'stats'])->name('campaign.stats');
});

Route::prefix('campaigns')->group(function () {

    // ── Public reads ──────────────────────────────────────────────────────
    Route::get('/{id}',            [CampaignController::class, 'show'])->name('campaign.show');

    // ── Protected writes (mosque manager only) ────────────────────────────
    Route::middleware(['auth:api', 'role:mosque_manager'])->group(function () {
        Route::get('/{id}/analytics',  [CampaignController::class, 'analytics'])->name('campaign.analytics');
        Route::post('/',       [CampaignController::class, 'store'])->name('campaign.store');
        Route::put('/{id}',    [CampaignController::class, 'update'])->name('campaign.update');
        Route::delete('/{id}', [CampaignController::class, 'destroy'])->name('campaign.destroy');
    });
});


