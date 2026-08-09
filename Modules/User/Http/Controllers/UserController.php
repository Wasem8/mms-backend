<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\User\Actions\ChangeUserStatusAction;
use Modules\User\Models\User;

class UserController extends Controller
{
    public function changeStatus(
        Request $request,
        User $user,
        ChangeUserStatusAction $action
    ) {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $updatedUser = $action->execute(
            $request->user(),
            $user,
            $validated['status']
        );

        return ApiResponse::success(
            [
                'id' => $updatedUser->id,
                'name' => $updatedUser->name,
                'email' => $updatedUser->email,
                'status' => $updatedUser->status,
            ],
            $validated['status'] === 'inactive'
                ? 'تم تجميد الحساب بنجاح.'
                : 'تم تفعيل الحساب بنجاح.'
        );
    }
}
