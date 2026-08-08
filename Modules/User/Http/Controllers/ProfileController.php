<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\User\Http\Requests\UpdateProfileRequest as RequestsUpdateProfileRequest;
use Modules\User\Transformers\ProfileResource;
use Modules\User\Services\ProfileService;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profileService) {}


    public function show(): JsonResponse
    {
        $profile = $this->profileService->getProfile(Auth::user());

        return ApiResponse::success(new ProfileResource($profile), 'تم جلب البيانات بنجاح');
    }


    public function update(RequestsUpdateProfileRequest $request): JsonResponse
    {
        $profile = $this->profileService->updateProfile(Auth::user(), $request->toDTO());

        return ApiResponse::success(new ProfileResource($profile), 'تم تحديث البيانات بنجاح');
    }
}
