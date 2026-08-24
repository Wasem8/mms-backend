<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\User\Actions\ChangeUserStatusAction;
use Modules\User\Models\User;
use Modules\User\Transformers\UserResource;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $currentUser = $request->user();

        if (!$currentUser) {
            return ApiResponse::error('غير مصرح لك بالوصول', 401);
        }

        // 🎯 شحن علاقة الأدوار والبرميشنز مسبقاً لتفادي مشكلة N+1 Query
        $query = User::with(['roles.permissions'])->latest();

        // 🎯 التراتبية والصلاحيات حسب المسجد (إذا لم يكن super_admin)
        if (!$currentUser->hasRole('super_admin') && $currentUser->mosque_id) {
            $query->where('mosque_id', $currentUser->mosque_id);
        }

        // 🔍 1. البحث بالاسم أو البريد الإلكتروني أو رقم الجوال
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 🔍 2. فلترة اختيارية حسب الحالة (active / inactive)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 🔍 3. فلترة اختيارية حسب الدور الوظيفي (role)
        if ($request->filled('role')) {
            $role = $request->input('role');
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        $users = $query->paginate(15);

        return ApiResponse::success(
            UserResource::collection($users),
            'تم جلب قائمة المستخدمين بنجاح.',
            $users
        );
    }
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
