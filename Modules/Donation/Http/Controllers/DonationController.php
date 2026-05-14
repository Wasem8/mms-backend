<?php

namespace Modules\Donation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Donation\Http\Requests\StoreDonationRequest;
use Modules\Donation\Services\DonationService;

class DonationController extends Controller
{
    protected DonationService $donationService;

    public function __construct(DonationService $donationService)
    {
        $this->donationService = $donationService;
    }

    public function index()
    {
        $donations = $this->donationService->getAllDonations();

        return ApiResponse::success($donations, 'Donations retrieved successfully');
    }

    public function store(StoreDonationRequest $request)
    {
        $data = $request->validated();
        $type = $data['type'];

        if ($type === 'kind') {
            $donation = $this->donationService->createDonation($data);

            return ApiResponse::success($donation, 'Donation created successfully');
        }

        $data['payment_method'] = $request->input('payment_method', 'cash');
        $result = $this->donationService->createDonationWithPayment($data);

        if ($result['donation'] === null) {
            return ApiResponse::error($result['payment']['message'] ?? 'Payment processing failed', 422);
        }

        return ApiResponse::success([
            'donation' => $result['donation'],
            'payment' => $result['payment'],
        ], 'Donation created successfully');
    }

    public function show($id)
    {
        $donation = $this->donationService->getDonationById($id);

        if (!$donation) {
            return ApiResponse::error('Donation not found', 404);
        }

        return ApiResponse::success($donation, 'Donation retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $donation = $this->donationService->getDonationById($id);

        if (!$donation) {
            return ApiResponse::error('Donation not found', 404);
        }

        if (! $this->canManageDonation($donation)) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $rules = [
            'mosque_id' => ['sometimes', 'integer', 'exists:mosques,id'],
            'mosque_need_id' => ['nullable', 'integer', 'exists:mosque_needs,id'],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'type' => ['sometimes', 'string', 'in:cash,kind'],
            'item_description' => ['sometimes', 'required_if:type,kind', 'string', 'max:255'],
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:999999'],
            'donor_name' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:pending,completed'],
        ];

        $data = $request->validate($rules);

        $donation = $this->donationService->updateDonation($id, $data);

        return ApiResponse::success($donation, 'Donation updated successfully');
    }

    public function destroy($id)
    {
        $donation = $this->donationService->getDonationById($id);

        if (!$donation) {
            return ApiResponse::error('Donation not found', 404);
        }

        if (! $this->canManageDonation($donation)) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $this->donationService->deleteDonation($id);

        return ApiResponse::success(null, 'Donation deleted successfully');
    }

    private function canManageDonation($donation): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole(['mosque_manager'])) {
            return true;
        }

        return isset($donation->user_id) && $donation->user_id === $user->id;
    }
}
