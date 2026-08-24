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
use Modules\Mosque\Models\Mosque;
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


        return ApiResponse::success($transformed, __('messages.community.tameems_retrieved'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'recipient_ids'   => 'nullable|array',
            'recipient_ids.*' => [
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = User::find($value);
                    if (!$user || !$user->hasRole('mosque_manager')) {
                        $fail(__('messages.community.invalid_recipient'));
                    }
                },
            ],
            'all_mosque_managers' => 'nullable|boolean',
        ]);

        if (!empty($validatedData['all_mosque_managers'])) {
            // إرسال لكل مدراء المساجد دون الحاجة لكتابة المعرّفات
            $recipientIds = User::role('mosque_manager')->pluck('id')->toArray();
        } elseif (!empty($validatedData['recipient_ids'])) {
            $recipientIds = $validatedData['recipient_ids'];
        } else {
            return ApiResponse::error(
                ['message' => __('messages.community.recipients_required')],
                422
            );
        }

        $senderId = auth()->id();

        $tameem = $this->tameemService->sendTameem($validatedData, $senderId, $recipientIds);

        return ApiResponse::success($this->transformTameem($tameem), __('messages.community.tameem_sent'));    }

    /**
     * Mosque manager sends a tameem to the supervisors and teachers of their own mosque.
     * The mosque is derived from the authenticated manager — no mosque id is required in the request.
     */
    public function storeForMosque(Request $request)
    {
        $manager = auth()->user();

        $mosque = $manager->managedMosque;

        if (!$mosque) {
            return ApiResponse::error(
                ['message' => __('messages.community.manager_without_mosque')],
                422
            );
        }

        $validatedData = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'recipient_ids'   => 'nullable|array',
            'recipient_ids.*' => [
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($mosque) {
                    $user = User::find($value);

                    if (!$user || !$user->hasAnyRole(['halaqa_supervisor', 'teacher'])) {
                        $fail(__('messages.community.invalid_recipient'));
                        return;
                    }

                    if ($user->mosque_id !== $mosque->id) {
                        $fail(__('messages.community.recipient_not_in_mosque'));
                    }
                },
            ],
            'all_staff'      => 'nullable|boolean',
            'all_teachers'   => 'nullable|boolean',
            'all_supervisors' => 'nullable|boolean',
        ]);

        if (!empty($validatedData['all_teachers']) || !empty($validatedData['all_supervisors']) || !empty($validatedData['all_staff'])) {
            // إرسال لكل المستهدفين في المسجد دون كتابة المعرّفات
            $roles = [];
            if (!empty($validatedData['all_staff']) || !empty($validatedData['all_teachers'])) {
                $roles[] = 'teacher';
            }
            if (!empty($validatedData['all_staff']) || !empty($validatedData['all_supervisors'])) {
                $roles[] = 'halaqa_supervisor';
            }
            $recipientIds = User::where('mosque_id', $mosque->id)
                ->whereHas('roles', fn($q) => $q->whereIn('name', $roles))
                ->pluck('id')
                ->all();
        } elseif (!empty($validatedData['recipient_ids'])) {
            $recipientIds = $validatedData['recipient_ids'];
        } else {
            return ApiResponse::error(
                ['message' => __('messages.community.recipients_required')],
                422
            );
        }

        $tameem = $this->tameemService->sendTameem(
            $validatedData,
            $manager->id,
            $recipientIds
        );

        return ApiResponse::success($this->transformTameem($tameem), __('messages.community.tameem_sent'));
    }

    public function show(int $id)
    {
        $tameem = $this->tameemService->getTameemById($id);

        if (!$tameem) {
            return ApiResponse::error(['message' => __('messages.community.tameem_not_found')], 404);
        }

        return ApiResponse::success($this->transformTameem($tameem), __('messages.community.tameem_retrieved'));    }

    public function update(UpdateTameemRequest $request, int $id)
    {
        try {
            $tameem = $this->tameemService->updateTameem(
                id: $id,
                data: $request->validated(),
                actorId: auth()->id(),
            );

            return ApiResponse::success($this->transformTameem($tameem), __('messages.community.tameem_updated'));
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

            return ApiResponse::success(null, __('messages.community.tameem_deleted'));
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

        return ApiResponse::success($transformed, __('messages.community.incoming_tameems_retrieved'));
          }

    public function sentTameems()
    {
        $senderId = auth()->id();
        $tameems = $this->tameemService->getSentTameems($senderId);

        $transformed = $tameems->map(fn($tameem) => $this->transformTameem($tameem));

        return ApiResponse::success($transformed, __('messages.community.sent_tameems_retrieved'));
    }

    public function markAsRead($id)
    {
        $mosqueManagerId = auth()->id();
        $this->tameemService->markTameemAsRead($id, $mosqueManagerId);

        return ApiResponse::success(null, __('messages.community.tameem_marked_read'));
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
