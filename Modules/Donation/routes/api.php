<?php

use Illuminate\Support\Facades\Route;
use Modules\Donation\Http\Controllers\CampaignController;
use Modules\Donation\Http\Controllers\DonationController;
use Modules\Donation\Http\Controllers\DonationController as DonationControllerAlias;
use Modules\Donation\Http\Controllers\SettingController;
use Modules\Donation\Http\Controllers\StripeWebhookController;


Route::post('stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::middleware(['auth:api', 'role:super_admin'])->group(function () {
    Route::put('settings/exchange-rate', [SettingController::class, 'updateExchangeRate']);
});

Route::get('settings', [SettingController::class, 'index']);



Route::prefix('mosques/{mosqueId}/donations')->group(function () {
    Route::get('/',        [DonationController::class, 'index']);
    Route::get('/summary', [DonationController::class, 'summary']);
    Route::get('/chart',   [DonationController::class, 'chart']);
});

Route::prefix('donations')->group(function () {
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/mine', [DonationController::class, 'mine']);
        Route::post('/admin/cash', [DonationController::class, 'storeCash']);
        Route::put('/{id}',    [DonationController::class, 'update']);
        Route::delete('/{id}', [DonationController::class, 'destroy']);
    });

    Route::get('/{reference}', [DonationController::class, 'show']);
    Route::post('/online', [DonationController::class, 'storeOnline']);
    Route::get('/{id}/receipt', [DonationControllerAlias::class, 'receipt'])->name('donations.receipt');
});


Route::prefix('mosques/{mosqueId}/campaigns')->group(function () {
    Route::get('/',      [CampaignController::class, 'index']);
    Route::get('/stats', [CampaignController::class, 'stats']);
});

Route::prefix('campaigns')->group(function () {
    Route::get('/{id}',           [CampaignController::class, 'show']);
    Route::get('/{id}/analytics', [CampaignController::class, 'analytics']);

    Route::middleware(['auth:api', 'role:mosque_manager'])->group(function () {
        Route::post('/',       [CampaignController::class, 'store']);
        Route::put('/{id}',    [CampaignController::class, 'update']);
        Route::delete('/{id}', [CampaignController::class, 'destroy']);
    });
});



Route::prefix('mosques/{mosqueId}/campaigns')->group(function () {
    Route::get('/',       [CampaignController::class, 'index'])->name('campaign.index');
    Route::get('/stats',  [CampaignController::class, 'stats'])->name('campaign.stats');
});

Route::prefix('campaigns')->group(function () {

    Route::get('/{id}',            [CampaignController::class, 'show'])->name('campaign.show');

    Route::middleware(['auth:api', 'role:mosque_manager'])->group(function () {
        Route::get('/{id}/analytics',  [CampaignController::class, 'analytics'])->name('campaign.analytics');
        Route::post('/',       [CampaignController::class, 'store'])->name('campaign.store');
        Route::put('/{id}',    [CampaignController::class, 'update'])->name('campaign.update');
        Route::delete('/{id}', [CampaignController::class, 'destroy'])->name('campaign.destroy');
    });
});
