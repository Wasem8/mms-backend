<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueSpace;
use Modules\Mosque\Services\MosqueSpaceService;

class MosqueSpaceController extends Controller
{
    public function __construct(
        private readonly MosqueSpaceService $spaceService
    ) {}


    public function index(Mosque $mosque)
    {
        try {
            $spaces = $this->spaceService->getSpacesByMosque($mosque->id);

            return ApiResponse::success(
                $spaces,
                'تم جلب مساحات المسجد بنجاح'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('حدث خطأ أثناء جلب البيانات', 500, $e->getMessage());
        }
    }

    /**
     * عرض مساحة محددة
     */
    public function show(Mosque $mosque, MosqueSpace $space)
    {
        // التحقق من أن المساحة تابعة للمسجد المطلوب
        if ($space->mosque_id !== $mosque->id) {
            return ApiResponse::error('هذه المساحة لا تنتمي لهذا المسجد', 404);
        }

        return ApiResponse::success($space, 'تم جلب تفاصيل المساحة بنجاح');
    }


    public function store(Request $request, Mosque $mosque)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
        ]);

        try {
            $validatedData['mosque_id'] = $mosque->id;

            $space = $this->spaceService->createSpace($validatedData);

            return ApiResponse::success($space, 'تمت إضافة المساحة بنجاح', null);
        } catch (\Exception $e) {
            return ApiResponse::error('حدث خطأ أثناء إضافة المساحة', 500, $e->getMessage());
        }
    }


    public function update(Request $request, Mosque $mosque, MosqueSpace $space)
    {
        if ($space->mosque_id !== $mosque->id) {
            return ApiResponse::error('هذه المساحة لا تنتمي لهذا المسجد', 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1',
        ]);

        try {
            $updatedSpace = $this->spaceService->updateSpace($space, $validatedData);

            return ApiResponse::success($updatedSpace, 'تم تحديث المساحة بنجاح');
        } catch (\Exception $e) {
            return ApiResponse::error('حدث خطأ أثناء تحديث المساحة', 500, $e->getMessage());
        }
    }

    /**
     * حذف مساحة
     */
    public function destroy(Mosque $mosque, MosqueSpace $space)
    {
        if ($space->mosque_id !== $mosque->id) {
            return ApiResponse::error('هذه المساحة لا تنتمي لهذا المسجد', 404);
        }

        try {
            $this->spaceService->deleteSpace($space);

            return ApiResponse::success([], 'تم حذف المساحة بنجاح');
        } catch (\Exception $e) {
            return ApiResponse::error('حدث خطأ أثناء حذف المساحة', 500, $e->getMessage());
        }
    }
}
