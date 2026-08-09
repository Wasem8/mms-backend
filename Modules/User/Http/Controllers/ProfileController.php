<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Actions\UpdateProfileAction;
use Modules\User\Http\Requests\UpdateProfileRequest;
use Modules\User\Transformers\ProfileResource;

class ProfileController extends Controller
{
    // عرض البروفايل الحالي للمستخدم المسجل دخول
    public function show()
    {
        $user = auth()->user()->load('mosque');

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب البيانات بنجاح',
            'data'    => new ProfileResource($user)
        ]);
    }

    // تحديث بيانات البروفايل
    public function update(UpdateProfileRequest $request, UpdateProfileAction $action)
    {
        $result = $action->execute($request->user(), $request->validated());
        $user = $result['user'];

        $message = $result['email_changed']
            ? 'تم تحديث البيانات الشخصية، وتم إرسال رمز التحقق إلى البريد الجديد لتأكيده.'
            : 'تم تحديث الملف الشخصي بنجاح';

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => new ProfileResource($user->load('mosque'))
        ]);
    }

    // تأكيد تغيير البريد الإلكتروني المعلق عبر OTP
    public function confirmEmailChange(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (!$user->pending_email) {
            return response()->json([
                'status'  => false,
                'message' => 'لا يوجد طلب تغيير بريد إلكتروني معلق.',
            ], 400);
        }

        // التحقق من صحة الـ OTP باستخدام دالة الموديل المعتمدة لديكم
        if (!$user->verifyOtp($request->otp)) {
            return response()->json([
                'status'  => false,
                'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            ], 422);
        }

        // اعتماد البريد الجديد وتصفير الحقل المؤقت
        $user->forceFill([
            'email'             => $user->pending_email,
            'pending_email'     => null,
            'email_verified_at' => now(),
        ])->save();

        $user->clearOtp();

        return response()->json([
            'status'  => true,
            'message' => 'تم تغيير البريد الإلكتروني وتأكيده بنجاح.',
            'data'    => new ProfileResource($user->load(['mosque', 'roles'])),
        ]);
    }
}
