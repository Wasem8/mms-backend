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

        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('غير مصرح لك بالوصول', 401);
        }

        $mosqueId = $user->mosque_id ?? $request->input('mosque_id');


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

        $messages = [
            'required'  => 'حقل :attribute مطلوب ولا يمكن تركه فارغاً.',
            'min'       => 'حقل :attribute يجب ألا يقل عن :min أحرف أو رموز.',
            'confirmed' => 'كلمة المرور وتأكيد كلمة المرور غير متطابقين.',
            'string'    => 'حقل :attribute يجب أن يكون نصاً صالحاً.',
        ];


        $attributes = [
            'name'     => 'الاسم الكامل',
            'password' => 'كلمة المرور الجديده',
            'token'    => 'رمز الدعوة',
        ];


        $validator = Validator::make($request->all(), [
            'token'    => 'required|string',
            'name'     => 'required|string',
            'password' => 'required|min:6|confirmed',
        ], $messages, $attributes);

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

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('غير مصرح لك بالوصول', 401);
        }

        $invitation = Invitation::findOrFail($id);

        // فقط الشخص الذي أنشأ الدعوة يستطيع حذفها
        if ((int) $invitation->created_by !== (int) $user->id) {
            return ApiResponse::error(
                'غير مصرح لك بحذف هذه الدعوة. يمكنك حذف الدعوات التي قمت بإنشائها فقط.',
                403
            );
        }

        // لا يمكن حذف دعوة تم قبولها
        if ($invitation->accepted_at !== null) {
            return ApiResponse::error(
                'لا يمكن حذف الدعوة لأن المستخدم قام بقبولها بالفعل.',
                422
            );
        }

        $invitation->delete();

        return ApiResponse::success(
            null,
            'تم حذف الدعوة بنجاح.'
        );
    }
    public function showAcceptForm(Request $request)
    {
        $token = $request->query('token');
        $invitation = Invitation::where('token', $token)->first();


        $isValid = $invitation && $invitation->isValid();

        if (! $isValid) {

            return view('invitation::accept', [
                'invitation' => $invitation,
                'is_expired' => true
            ]);
        }

        return view('invitation::accept', [
            'invitation' => $invitation,
            'is_expired' => false
        ]);
    }

    public function resend($id, SendInvitationAction $action)
    {
        $invitation = Invitation::findOrFail($id);

        // تحقق من الصلاحيات (أن المستخدم الحالي هو مدير المسجد أو صاحب الدعوة)
        $user = request()->user();
        if (!$user->hasRole('super_admin') && $invitation->mosque_id !== $user->mosque_id) {
            return ApiResponse::error('غير مصرح لك بإعادة إرسال هذه الدعوة', 403);
        }

        $action->resend($invitation);

        return ApiResponse::success(
            new InvitationResource($invitation),
            'تمت إعادة إرسال الدعوة بنجاح وتمديد صلاحيتها.'
        );
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('غير مصرح لك بالوصول', 401);
        }

        $query = Invitation::with('mosque')->latest();

        // 🎯 الصلاحيات: مدير المسجد يرى كافة دعوات المسجد، وغيره يرى فقط الدعوات التي أنشأها بنفسه
        if ($user->hasRole('mosque_manager')) {
            $query->where('mosque_id', $user->mosque_id);
        } else {
            $query->where('created_by', $user->id);
        }

        // 🔍 فلترة اختيارية حسب حالة الدعوة (pending, accepted, expired)
        if ($request->filled('status')) {
            $status = $request->input('status');

            if ($status === 'accepted') {
                $query->whereNotNull('accepted_at');
            } elseif ($status === 'expired') {
                $query->whereNull('accepted_at')->where('expires_at', '<=', now());
            } elseif ($status === 'pending') {
                $query->whereNull('accepted_at')->where('expires_at', '>', now());
            }
        }

        $invitations = $query->paginate(15);

        // 🎯 تمرير $invitations في البرامتر الثالث ليتكفل ApiResponse بتنسيق Pagination تلقائياً
        return ApiResponse::success(
            InvitationResource::collection($invitations),
            'تم جلب الدعوات بنجاح.',
            $invitations
        );
    }
}
