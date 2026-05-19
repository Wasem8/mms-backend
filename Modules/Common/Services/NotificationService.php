<?php

namespace Modules\Common\Services;

use Modules\Common\Models\Notification as NotificationModel;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notify($user, $title, $body, $type, array $data = [])
    {
        $notification = NotificationModel::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'data'    => $data,
        ]);

        if ($user->fcm_token) {
            try {
                $messaging = app('firebase.messaging');

                $message = CloudMessage::fromArray([
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => [
                        'notification_id' => (string) $notification->id,
                        'type'            => (string) $type,
                        'extra_data'      => json_encode($data, JSON_UNESCAPED_UNICODE)
                    ],
                ]);

                $messaging->send($message);

                Log::info("Firebase Notification Sent to User: " . $user->id);

            } catch (\Exception $e) {
                Log::error("Firebase Error: " . $e->getMessage());
            }
        }
    }
}
