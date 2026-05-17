<?php

namespace Modules\Common\Services;

use Modules\Common\Models\Notification as NotificationModel;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notify($user, $title, $body, $type, array $data = [])
    {
        // 1. الحفظ في الداتابيز
        NotificationModel::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'data'    => $data,
        ]);

        // 2. الإرسال لـ Firebase (في حال وجود توكن)
        if ($user->fcm_token) {
            try {
                $messaging = app('firebase.messaging');

//                // الطريقة الصحيحة للإصدارات الجديدة
//                $message = CloudMessage::withTarget('token', $user->fcm_token)
//                    ->withNotification(FirebaseNotification::create($title, $body))
//                    ->withData(array_merge(['type' => $type], $data));
//
                // ملاحظة: إذا استمر الخطأ في withTarget، جرب استخدام الكود التالي:

                $message = CloudMessage::fromArray([
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_merge(['type' => $type], $data),
                ]);


                $messaging->send($message);

                Log::info("Firebase Notification Sent to User: " . $user->id);

            } catch (\Exception $e) {
                Log::error("Firebase Error: " . $e->getMessage());
            }
        }
    }
}
