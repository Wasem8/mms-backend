<?php

namespace Modules\Donation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Donation\Http\Requests\StoreCashDonationRequest;
use Modules\Donation\Http\Requests\StoreDonationRequest;
use Modules\Donation\Http\Requests\StoreOnlineDonationRequest;
use Modules\Donation\Http\Requests\UpdateDonationRequest;
use Modules\Donation\Models\Donation;
use Modules\Donation\Services\DonationService;
use Modules\Donation\Transformers\DonationResource;

class DonationController extends Controller
{
    public function __construct(
        protected DonationService $donationService,
    ) {}

    public function index(int $mosqueId)
    {
        $filters   = request()->only(['search', 'type', 'status', 'campaign', 'per_page']);
        $donations = $this->donationService->getByMosque($mosqueId, $filters);

        return DonationResource::collection($donations)->response();
    }

    public function mine()
    {
        $userId    = auth()->guard('api')->id();
        $filters   = request()->only(['search', 'type', 'status', 'campaign', 'per_page']);
        $donations = $this->donationService->getByUser($userId, $filters);

        return DonationResource::collection($donations)->response();
    }

    /**
     * Super-admin: list all donations across all mosques (paginated).
     */
    public function allDonations(\Illuminate\Http\Request $request)
    {
        $filters   = $request->only(['search', 'type', 'status', 'campaign', 'mosque_id', 'city', 'date_from', 'date_to', 'per_page']);
        $donations = $this->donationService->getAllDonations($filters);

        return DonationResource::collection($donations)->response();
    }


    public function summary(int $mosqueId)
    {
        $summary = $this->donationService->getDailySummary($mosqueId);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $summary,
        ]);
    }

    /**
     * Donation page stats.
     * - Super-admin: aggregated across ALL mosques.
     * - Any other authenticated user: scoped to their own donations.
     * No mosque id is required — the scope is derived from the authenticated user.
     */
    public function stats()
    {
        $user = auth()->guard('api')->user();

        if ($user->hasRole('super_admin')) {
            $data = $this->donationService->getPageStatsForAll();
        } elseif ($user->hasRole('mosque_manager') && $user->managedMosque) {
            $data = $this->donationService->getPageStats($user->managedMosque->id);
        } else {
            $data = $this->donationService->getPageStatsForUser($user->id);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $data,
        ]);
    }

    public function recentDonations(int $mosqueId)
    {
        $limit = (int) request()->query('limit', 5);
        $data  = $this->donationService->getRecentDonations($mosqueId, $limit);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $data,
        ]);
    }
    public function chart(int $mosqueId)
    {
        $data = $this->donationService->getMonthlyDistribution($mosqueId);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $data,
        ]);
    }

    /**
     * Donation report for the authenticated mosque manager.
     * The mosque is derived from the manager's managedMosque (no mosque id required).
     * - Without `?export=pdf` returns the report data as JSON.
     * - With `?export=pdf` generates a PDF and returns a Supabase signed download URL
     *   (same export flow used for donation receipts and volunteer certificates).
     */
    public function report(\Illuminate\Http\Request $request)
    {
        $manager = auth()->user();
        $mosque  = $manager->managedMosque;

        if (!$mosque) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.mosque.manager_without_mosque'),
            ], 422);
        }

        $filters = $request->only(['search', 'type', 'status', 'campaign', 'date_from', 'date_to']);

        if ($request->query('export') === 'pdf') {
            $url = $this->donationService->exportReport($mosque->id, $filters);

            return response()->json([
                'status'  => true,
                'message' => 'تم إنشاء تقرير التبرعات بنجاح.',
                'data'    => ['report_url' => $url],
            ]);
        }

        $report = $this->donationService->getReport($mosque->id, $filters);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $report,
        ]);
    }

    /**
     * Super-admin donation report across ALL mosques.
     * - Without `?export=pdf` returns aggregated report data (JSON).
     * - With `?export=pdf` returns a Supabase signed download URL.
     * Supports the same filters as the mosque-manager report plus `mosque_id` and `city`.
     */
    public function allReport(\Illuminate\Http\Request $request)
    {
        $filters = $request->only(['search', 'type', 'status', 'campaign', 'mosque_id', 'city', 'date_from', 'date_to']);

        if ($request->query('export') === 'pdf') {
            $url = $this->donationService->exportReportForAll($filters);

            return response()->json([
                'status'  => true,
                'message' => 'تم إنشاء تقرير التبرعات لكل المساجد بنجاح.',
                'data'    => ['report_url' => $url],
            ]);
        }

        $report = $this->donationService->getReportForAll($filters);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $report,
        ]);
    }

    public function receipt($id)
    {
        $donation = Donation::findOrFail($id);

        $url = $this->donationService->getReceiptDownloadUrl($donation);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => ['receipt_url' => $url],
        ]);
    }



    public function show(string  $ref)
    {
        $donation = $this->donationService->findByReference($ref);

        return (new DonationResource($donation))->response();
    }

    public function storeOnline(StoreOnlineDonationRequest $request)
    {
        $data = $request->validated();

        if ($data['donation_type'] === 'cash') {
            $data['payment_method'] = 'stripe';
        } else {
            $data['payment_method'] = 'none';
        }

        $data['user_id'] = auth()->guard('api')->check() ? auth()->guard('api')->id() : null;

        $result = $this->donationService->create($data);

        $response = (new DonationResource($result['donation']))->toArray($request);

        if (!empty($result['client_secret'])) {
            $response['client_secret'] = $result['client_secret'];
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم إنشاء طلب التبرع بنجاح.',
            'data'    => $response,
        ], 201);
    }


    public function storeCash(StoreCashDonationRequest $request)
    {
        $authUser = auth()->guard('api')->user();

        if (!$authUser || !$authUser->hasRole('mosque_manager')) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح. مدراء المسجد فقط يمكنهم إضافة تبرعات نقدية.'
            ], 403);
        }

        $data = $request->validated();

        $data['payment_method'] = 'cash';


        $result = $this->donationService->create($data);


        return response()->json([
            'status'  => true,
            'message' => 'تم حفظ التبرع النقدي بنجاح.',
            'data'    => new DonationResource($result['donation']),
        ], 201);
    }
    /*
    public function store(StoreDonationRequest $request)
    {
        $result = $this->donationService->create($request->validated());

        $response = (new DonationResource($result['donation']))->toArray($request);

        if ($result['client_secret']) {
            $response['client_secret'] = $result['client_secret'];
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم حفظ التبرع بنجاح.',
            'data'    => $response,
        ], 201);
    }
*/

    public function update(UpdateDonationRequest $request, int $id)
    {
        $donation = $this->donationService->update($id, $request->validated());

        return (new DonationResource($donation))->response();
    }


    public function destroy(int $id)
    {
        $this->donationService->delete($id);

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف السجل بنجاح.',
            'data'    => null,
        ]);
    }
}
