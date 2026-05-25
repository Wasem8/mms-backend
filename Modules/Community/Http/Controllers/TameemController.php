<?php

namespace Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Community\Http\Requests\UpdateTameemRequest;
use Modules\Community\Services\TameemService;
use Modules\User\Models\User;
class TameemController extends Controller
{
    protected $tameemService;

    public function __construct(TameemService $tameemService)
    {
        $this->tameemService = $tameemService;
    }

    public function index()
    {
        $tameems = $this->tameemService->getAllTameems();

        $transformed = $tameems->map(fn($tameem) => $this->transformTameem($tameem));


        return ApiResponse::success($transformed,'تم جلب التعاميم بنجاح');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'recipient_ids'   => 'required|array',
            'recipient_ids.*' => [
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = User::find($value);
                    if (!$user || !$user->hasRole('mosque_manager')) {
                        $fail('أحد المستلمين غير موجود أو ليس مدير مسجد.');
                    }
                },
            ],
        ]);
        $senderId = auth()->id();
        $recipientIds = $validatedData['recipient_ids'];

        $tameem = $this->tameemService->sendTameem($validatedData, $senderId, $recipientIds);

        return ApiResponse::success($this->transformTameem($tameem), 'تم إرسال التعميم بنجاح');    }

    public function show(int $id)
    {
        $tameem = $this->tameemService->getTameemById($id);

        if (!$tameem) {
            return ApiResponse::error(['message' => 'التعميم غير موجود'], 404);
        }

        return ApiResponse::success($this->transformTameem($tameem), 'تم جلب التعميم بنجاح');    }

    public function update(UpdateTameemRequest $request, int $id)
    {
        try {
            $tameem = $this->tameemService->updateTameem(
                id: $id,
                data: $request->validated(),
                actorId: auth()->id(),
            );

            return ApiResponse::success($this->transformTameem($tameem), 'تم تحديث التعميم بنجاح');
            } catch (AuthorizationException $e) {
            return ApiResponse::error(['message' => $e->getMessage()], 403);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->tameemService->deleteTameem(
                id: $id,
                actorId: auth()->id(),
            );

            return ApiResponse::success(null, 'تم حذف التعميم بنجاح');
        } catch (AuthorizationException $e) {
            return ApiResponse::error(['message' => $e->getMessage()], 403);
        }
    }

    public function myTameems()
    {
        $mosqueManagerId = auth()->id();
        $tameems = $this->tameemService->getMosqueManagerTameems($mosqueManagerId);

        // تحويل التعاميم الواردة لمدير المسجد ليعرف حالة القراءة بدقة
        $transformed = $tameems->map(fn($tameem) => $this->transformTameem($tameem));

        return ApiResponse::success($transformed, 'تم جلب التعاميم الواردة بنجاح');
          }

    public function markAsRead($id)
    {
        $mosqueManagerId = auth()->id();
        $this->tameemService->markTameemAsRead($id, $mosqueManagerId);

        return ApiResponse::success(null, 'تم تحديث حالة التعميم إلى مقروء');
    }

    private function transformTameem($tameem): array
    {
        return [
            'id' => $tameem->id,
            'title' => $tameem->title,
            'content' => $tameem->content,
            'sender_id' => $tameem->sender_id,
            'sent_at' => $tameem->sent_at,
            'created_at' => $tameem->created_at,
            'updated_at' => $tameem->updated_at,

            'recipients' => $tameem->recipients->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'is_read' => $user->pivot ? (bool) $user->pivot->is_read : false,
                    'read_at' => $user->pivot ? $user->pivot->read_at : null,
                ];
            })->toArray(),
        ];
    }
}
