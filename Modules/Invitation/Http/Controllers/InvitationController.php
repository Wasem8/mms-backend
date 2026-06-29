<?php

namespace Modules\Invitation\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Invitation\Actions\SendInvitationAction;
use Modules\Invitation\Actions\AcceptInvitationAction;
use Modules\Invitation\Http\Requests\SendInvitationRequest;
use Modules\Invitation\Http\Requests\AcceptInvitationRequest;
use Modules\Invitation\Models\Invitation;
use Modules\Invitation\Transformers\InvitationResource;

class InvitationController
{
    public function send(SendInvitationRequest $request, SendInvitationAction $action)
    {
        // 🎯 التحقق من أن المستخدم مسجل دخول بالفعل وليس null
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('غير مصرح لك بالوصول', 401);
        }

        $mosqueId = $user->mosque_id ?? $request->input('mosque_id');

        // تنفيذ الأكشن بأمان بعد التأكد من وجود المستخدم
        $invitation = $action->execute(
            $user,
            $request->email,
            $request->role,
            $mosqueId
        );

        return ApiResponse::success(new InvitationResource($invitation), 'Invitation sent successfully.');
    }



    public function accept(Request $request, AcceptInvitationAction $action)
    {
        // 1. تعريف رسائل الخطأ المخصصة باللغة العربية
        $messages = [
            'required'  => 'حقل :attribute مطلوب ولا يمكن تركه فارغاً.',
            'min'       => 'حقل :attribute يجب ألا يقل عن :min أحرف أو رموز.',
            'confirmed' => 'كلمة المرور وتأكيد كلمة المرور غير متطابقين.',
            'string'    => 'حقل :attribute يجب أن يكون نصاً صالحاً.',
        ];

        // 2. تعريب أسماء الحقول لكي تظهر بشكل أنيق داخل الرسالة
        $attributes = [
            'name'     => 'الاسم الكامل',
            'password' => 'كلمة المرور الجديده',
            'token'    => 'رمز الدعوة',
        ];

        // 3. إجراء الفحص وتمرير المصفوفات الجديدة
        $validator = Validator::make($request->all(), [
            'token'    => 'required|string',
            'name'     => 'required|string',
            'password' => 'required|min:6|confirmed',
        ], $messages, $attributes); // 🎯 قمنا بإضافة المصفوفات هنا

        // 4. إذا فشل الفحص، سيتم التوجيه بالرسائل العربية
        if ($validator->fails()) {

            if (! $request->wantsJson()) {
                return redirect()->route('invitations.accept_form', ['token' => $request->input('token')])
                    ->withErrors($validator)
                    ->withInput();
            }

            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'data' => $validator->errors(),
                'pagination' => null
            ], 422);
        }
        try {
            // 3. إذا نجح الفحص، نقوم بتنفيذ الأكشن الأصلي
            $result = $action->execute($validator->validated());

            if (! $request->wantsJson()) {
                return view('invitation::success', [
                    'user' => $result['user'],
                    'role' => $result['role']
                ]);
            }

            return ApiResponse::success([
                'user' => $result['user'],
                'user_status' => $result['is_new_user'] ? 'new' : 'existing',
                'roles_added' => [$result['role']],
            ], 'Invitation accepted successfully.');

        } catch (ValidationException $e) {

            if (! $request->wantsJson()) {
                return redirect()->route('invitations.accept_form', ['token' => $request->input('token')])
                    ->withErrors($e->validator)
                    ->withInput();
            }
            throw $e;
        }
    }
    public function showAcceptForm(Request $request)
    {
        $token = $request->query('token');
        $invitation = Invitation::where('token', $token)->first();

        // بدلاً من abort(404)، يمكننا تمرير متغير فحص الصلاحية للـ Blade
        $isValid = $invitation && $invitation->isValid();

        if (! $isValid) {
            // يمكنك إما توجيهه لصفحة مخصصة أو تمرير خطأ مخصص
            return view('invitation::accept', [
                'invitation' => $invitation,
                'is_expired' => true // نمرر هذا المتغير للبليد للتعامل معه بشكل جمالي
            ]);
        }

        return view('invitation::accept', [
            'invitation' => $invitation,
            'is_expired' => false
        ]);
    }
}
