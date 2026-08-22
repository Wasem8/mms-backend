<?php

namespace Modules\Common\Services;

use Modules\Common\Models\Notification as NotificationModel;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notify($user, $title, $body, $type, array $data = [])
    {
        /*
        |--------------------------------------------------------------------------
        | 1. إنشاء الإشعار في قاعدة البيانات
        |--------------------------------------------------------------------------
        */

        $notification = NotificationModel::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'data'    => $data,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. التحقق من وجود FCM Token
        |--------------------------------------------------------------------------
        */

        if (empty($user->fcm_token)) {

            Log::warning('FCM Token is missing', [
                'user_id' => $user->id,
            ]);

            return $notification;
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | 3. الحصول على Firebase Messaging
            |--------------------------------------------------------------------------
            */

            $messaging = app('firebase.messaging');

            /*
            |--------------------------------------------------------------------------
            | 4. تجهيز Data
            |--------------------------------------------------------------------------
            |
            | FCM Data values يجب أن تكون strings.
            |
            */

            $firebaseData = [
                'notification_id' => (string) $notification->id,
                'type'            => (string) $type,
                'extra_data'      => json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ),
            ];

            /*
            |--------------------------------------------------------------------------
            | 5. إنشاء الرسالة
            |--------------------------------------------------------------------------
            */

            $message = CloudMessage::new()
                ->withToken($user->fcm_token)
                ->withNotification([
                    'title' => $title,
                    'body'  => $body,
                ])
                ->withData($firebaseData);

            /*
            |--------------------------------------------------------------------------
            | 6. إرسال الإشعار
            |--------------------------------------------------------------------------
            */

            $result = $messaging->send($message);

            /*
            |--------------------------------------------------------------------------
            | 7. تسجيل نجاح الإرسال
            |--------------------------------------------------------------------------
            */

            Log::info('✅ Firebase Notification Sent', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'fcm_token' => substr($user->fcm_token, 0, 20) . '...',
                'firebase_response' => $result,
            ]);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | تسجيل الخطأ الحقيقي
            |--------------------------------------------------------------------------
            */

            Log::error('❌ Firebase Notification Failed', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $notification;
    }
}
