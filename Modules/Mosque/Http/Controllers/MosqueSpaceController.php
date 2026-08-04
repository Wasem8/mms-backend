<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueSpace;
use Modules\Mosque\Services\MosqueSpaceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class MosqueSpaceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly MosqueSpaceService $spaceService
    ) {}


    public function index(Mosque $mosque)
    {
        try {
            $spaces = $this->spaceService->getSpacesByMosque($mosque->id);

            return ApiResponse::success(
                $spaces,
                __('messages.mosque.spaces_retrieved')
            );
        } catch (\Exception $e) {
            return ApiResponse::error(__('messages.mosque.spaces_fetch_error'), 500, $e->getMessage());
        }
    }

    /**
     * عرض مساحة محددة
     */
    public function show(Mosque $mosque, MosqueSpace $space)
    {
        // التحقق من أن المساحة تابعة للمسجد المطلوب
        if ($space->mosque_id !== $mosque->id) {
            return ApiResponse::error(__('messages.mosque.space_not_in_mosque'), 404);
        }

        return ApiResponse::success($space, __('messages.mosque.space_retrieved'));
    }


    public function store(Request $request, Mosque $mosque)
    {
        $this->authorize('manage', $mosque);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
        ]);

        try {
            $validatedData['mosque_id'] = $mosque->id;
            $space = $this->spaceService->createSpace($validatedData);

            return ApiResponse::success($space, __('messages.mosque.space_created'), null);
        } catch (\Exception $e) {
            return ApiResponse::error(__('messages.mosque.space_create_error'), 500, $e->getMessage());
        }
    }

    public function update(Request $request, Mosque $mosque, MosqueSpace $space)
    {

        $this->authorize('manage', $mosque);

        if ($space->mosque_id !== $mosque->id) {
            return ApiResponse::error(__('messages.mosque.space_not_in_mosque'), 404);
        }



        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1',
        ]);

        try {
            $updatedSpace = $this->spaceService->updateSpace($space, $validatedData);

            return ApiResponse::success($updatedSpace, __('messages.mosque.space_updated'));
        } catch (\Exception $e) {
            return ApiResponse::error(__('messages.mosque.space_update_error'), 500, $e->getMessage());
        }
    }

    /**
     * حذف مساحة
     */
    public function destroy(Mosque $mosque, MosqueSpace $space)
    {
        $this->authorize('manage', $mosque);

        if ($space->mosque_id !== $mosque->id) {
            return ApiResponse::error(__('messages.mosque.space_not_in_mosque'), 404);
        }
        try {
            $this->spaceService->deleteSpace($space);

            return ApiResponse::success([], __('messages.mosque.space_deleted'));
        } catch (\Exception $e) {
            return ApiResponse::error(__('messages.mosque.space_delete_error'), 500, $e->getMessage());
        }
    }
}
