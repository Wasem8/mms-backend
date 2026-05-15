<?php

namespace Modules\Common\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Common\Models\Notification;
use Modules\Common\Transformers\NotificationResource;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->when($request->has('is_read'), function ($query) use ($request) {
                $isRead = filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN);

                return $isRead
                    ? $query->whereNotNull('read_at')
                    : $query->whereNull('read_at');
            })
            ->paginate(15);

        return ApiResponse::success(
            NotificationResource::collection($notifications)->toArray($request),
            __('messages.notifications_retrieved'),
            $notifications
        );
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return ApiResponse::success(
            null,
            __('messages.notification_marked_read')
        );
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return ApiResponse::success(
            null,
            __('messages.notifications_all_marked_read')
        );
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->delete();

        return ApiResponse::success(
            null,
            __('messages.notification_deleted')
        );
    }
}
