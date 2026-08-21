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
    Route::get('admin/donations', [DonationController::class, 'allDonations']);
});

Route::get('settings', [SettingController::class, 'index']);



Route::prefix('mosques/{mosqueId}/donations')->middleware(['auth:api','role:mosque_manager'])->group(function () {
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

    // Page stats — no mosque id; scoped to the authenticated user (or all mosques for super-admin).
    // Registered before `/{reference}` so it is not captured by the reference route.
    Route::get('/stats', [DonationController::class, 'stats'])->middleware(['auth:api']);

    Route::get('/{reference}', [DonationController::class, 'show']);
    Route::post('/online', [DonationController::class, 'storeOnline']);
    Route::get('/{id}/receipt', [DonationControllerAlias::class, 'receipt'])->name('donations.receipt');
});


Route::prefix('mosques/{mosqueId}/campaigns')->group(function () {
    Route::get('/',      [CampaignController::class, 'showByMosque']);
    Route::get('/stats', [CampaignController::class, 'stats']);
});

Route::prefix('campaigns')->group(function () {
    Route::get('/',                [CampaignController::class, 'index']);
    Route::get('/{id}',           [CampaignController::class, 'show']);
    Route::get('/{id}/analytics', [CampaignController::class, 'analytics']);

    Route::middleware(['auth:api', 'role:mosque_manager'])->group(function () {
        Route::post('/',       [CampaignController::class, 'store']);
        Route::put('/{id}',    [CampaignController::class, 'update']);
        Route::delete('/{id}', [CampaignController::class, 'destroy']);
    });
});

Route::middleware(['auth:api', 'role:mosque_manager'])->group(function () {
    Route::get('mosque/campaigns', [CampaignController::class, 'mosqueIndex']);
});

Route::get('mosques/{mosqueId}/donations/recent', [DonationController::class, 'recentDonations'])->middleware(['auth:api','role:mosque_manager']);

Route::middleware(['auth:api', 'role:mosque_manager'])->group(function () {
    Route::get('mosque/donations/report', [DonationController::class, 'report']);
});

Route::middleware(['auth:api', 'role:super_admin'])->group(function () {
    Route::get('admin/donations/report', [DonationController::class, 'allReport']);
});
