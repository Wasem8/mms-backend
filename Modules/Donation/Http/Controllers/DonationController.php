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
        $filters   = request()->only(['search', 'type', 'status', 'campaign']);
        $donations = $this->donationService->getByMosque($mosqueId, $filters);

        return DonationResource::collection($donations)->response();
    }


// not work yet
    public function summary(int $mosqueId)
    {
        $summary = $this->donationService->getDailySummary($mosqueId);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $summary,
        ]);
    }

    // not work yet

    public function chart(int $mosqueId)
    {
        $data = $this->donationService->getMonthlyDistribution($mosqueId);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $data,
        ]);
    }

    public function receipt($id)
    {
        $donation = \Modules\Donation\Models\Donation::with(['mosque', 'campaign', 'mosqueNeed'])
            ->findOrFail($id);

        $pdf = $this->donationService->generateReceipt($donation);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-' . $donation->reference . '.pdf"',
        ]);
    }



    public function show(int $id)
    {
        $donation = $this->donationService->find($id);

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
