<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Mosque\Models\Mosque;
use App\Support\ApiResponse;
use Modules\Mosque\Http\Requests\StoreMosqueRequest;
use Modules\Mosque\Http\Requests\UpdateMosqueRequest;
use Modules\Mosque\Services\MosqueService;

class MosqueController extends Controller
{
    public function __construct(
        private readonly MosqueService $mosqueService
    ) {}


    public function nearby(Request $request)
    {
        $validated = $request->validate([
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'limit'       => 'nullable|integer|min:1|max:100',
            'city_id'     => ['nullable', 'integer', 'exists:cities,id'],       // ⬅ إضافة
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],    // ⬅ إضافة
            'facility_id' => ['nullable', 'integer', 'exists:facilities,id'],
        ]);

        $paginatedMosques = $this->mosqueService->getNearbyMosques($validated);

        return ApiResponse::success(
            $paginatedMosques->items(),
            'تم جلب المساجد الأقرب لموقعك بنجاح',
            $paginatedMosques
        );
    }


    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
            'city_id'     => ['nullable', 'integer', 'exists:cities,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'city'        => ['nullable', 'string'],
            'district'    => ['nullable', 'string'],
            'status'      => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'min_rating'  => ['nullable', 'numeric', 'min:0', 'max:5'],
            'has_imam'    => ['nullable', 'boolean'],
            'sort_by'     => ['nullable', 'string', 'in:name,city,district,average_rating,reviews_count,created_at'],
            'sort_order'  => ['nullable', 'string', 'in:asc,desc'],
            'facility_id' => ['nullable', 'integer', 'exists:facilities,id'],
        ]);

        $data = $this->mosqueService->getAllMosques(
            $validated,
            (int) ($validated['per_page'] ?? 15)
        );

        return ApiResponse::success(
            $data->items(),
            'Mosques retrieved successfully',
            ApiResponse::pagination($data)
        );
    }


    public function show(int $id)
    {
        $mosque = $this->mosqueService->getMosqueById($id);

        if (!$mosque) {
            return ApiResponse::error('Mosque not found', 404);
        }

        return ApiResponse::success($mosque, 'Mosque retrieved successfully');
    }


    public function store(StoreMosqueRequest $request)
    {
        $data = $request->validated();


        $mosque = $this->mosqueService->createMosque($data);

        return ApiResponse::success(
            $mosque,
            'Mosque created successfully',
            null,
            201
        );
    }


    public function update(UpdateMosqueRequest $request, Mosque $mosque)
    {
        $updated = $this->mosqueService->updateMosque(
            $mosque,
            $request->validated()
        );

        return ApiResponse::success(
            $updated,
            'Mosque updated successfully'
        );
    }


    public function destroy(Mosque $mosque)
    {
        $this->mosqueService->deleteMosque($mosque);

        return ApiResponse::success(null, 'Mosque deleted successfully');
    }


    public function byCity(string $city)
    {
        return ApiResponse::success(
            $this->mosqueService->getMosquesByCity($city),
            'Mosques retrieved successfully'
        );
    }


    public function featured(Request $request)
    {
        return ApiResponse::success(
            $this->mosqueService->getFeaturedMosques(
                (int) $request->get('limit', 10)
            ),
            'Featured mosques retrieved successfully'
        );
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q'           => ['required', 'string', 'min:1'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
            'city_id'     => ['nullable', 'integer', 'exists:cities,id'],       // ⬅ إضافة
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],    // ⬅ إضافة
            'facility_id' => ['nullable', 'integer', 'exists:facilities,id'],
        ]);

        $data = $this->mosqueService->searchMosques(
            $validated['q'],
            array_filter([
                'city_id' => $validated['city_id'] ?? null,        // ⬅ إضافة هون كمان
                'district_id' => $validated['district_id'] ?? null, // ⬅ وهون
                'facility_id' => $validated['facility_id'] ?? null,
            ]),
            (int) ($validated['per_page'] ?? 15)
        );

        return ApiResponse::success(
            $data->items(),
            'Search results retrieved successfully',
            ApiResponse::pagination($data)
        );
    }

    // -------------------------
    // TOGGLE FEATURED
    // -------------------------
    public function toggleFeatured(Mosque $mosque)
    {
        return ApiResponse::success(
            $this->mosqueService->toggleFeaturedStatus($mosque),
            'Feature status updated successfully'
        );
    }


    public function updateStatus(Request $request, Mosque $mosque)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        return ApiResponse::success(
            $this->mosqueService->updateMosqueStatus($mosque, $validated['status']),
            'Status updated successfully'
        );
    }

    // -------------------------
    // UPDATE RATING
    // -------------------------
    public function updateRating(Request $request, Mosque $mosque)
    {
        $validated = $request->validate([
            'average_rating' => ['required', 'numeric'],
            'reviews_count' => ['required', 'integer'],
        ]);

        return ApiResponse::success(
            $this->mosqueService->updateMosqueRating(
                $mosque,
                (float) $validated['average_rating'],
                (int) $validated['reviews_count']
            ),
            'Rating updated successfully'
        );
    }
}
