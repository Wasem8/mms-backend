# Common Module Notification Usage

This document explains how to use the Common module notification service inside the `Modules/Common` module.
It includes the required code patterns and example usage for Claude code or implementation guidance.

## Notification flow

The Common module uses a custom notification service in `Modules/Common/Services/NotificationService.php`.
It does two things:

- creates a database record in `Modules/Common/Models/Notification.php`
- sends a Firebase push message if the user has `fcm_token`

## NotificationService implementation

```php
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
                        'extra_data'      => json_encode($data, JSON_UNESCAPED_UNICODE),
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
```

## Notification model

The notification record is stored in `Modules/Common/Models/Notification.php`.

```php
namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'type', 'data', 'read_at'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}
```

## How to send a notification

Use the service directly with a user entity.

```php
use Modules\Common\Services\NotificationService;

$notificationService = new NotificationService();
$notificationService->notify(
    $user,
    'Title of notification',
    'Body text for the user',
    'attendance_absence',
    [
        'student_id' => (string) $student->id,
        'date' => (string) $date,
    ]
);
```

### Required parameters

- `$user`: any authenticated user model instance that has `id` and optionally `fcm_token`
- `$title`: notification title string
- `$body`: notification body string
- `$type`: notification type identifier string
- `$data`: optional array payload saved to the database and forwarded to FCM as `extra_data`

## Example usage in a listener

The Common module already registers event listeners in `Modules/Common/Providers/EventServiceProvider.php`.
Here is a real listener example:

```php
namespace Modules\Common\Listeners;

use Modules\Education\Events\AttendanceRecorded;
use Modules\Common\Services\NotificationService;
use Modules\Education\Models\Student;

class SendAttendanceNotification
{
    public function handle(AttendanceRecorded $event)
    {
        $notificationService = new NotificationService();

        $studentIds = collect($event->records)->pluck('student_id');
        $students = Student::with('parent')->whereIn('id', $studentIds)->get()->keyBy('id');
        $halaqaId = isset($event->halaqaId) ? (string) $event->halaqaId : '';

        foreach ($event->records as $record) {
            $student = $students->get($record['student_id']);
            $parent = $student?->parent;

            if ($parent && in_array($record['status'], ['absent', 'late'])) {
                $statusText = $record['status'] == 'absent' ? 'غائباً' : 'متأخراً';
                $title = "تنبيه حضور: " . $student->first_name;
                $body = "نود إحاطتكم علماً بأن الطالب {$student->first_name} كان {$statusText} عن حلقة اليوم بتاريخ {$event->date}.";
                $type = 'attendance_absence';
                $extraData = [
                    'student_id' => (string) $student->id,
                    'halaqa_id'  => $halaqaId,
                    'date'       => (string) $event->date,
                    'status'     => (string) $record['status'],
                ];

                $notificationService->notify($parent, $title, $body, $type, $extraData);
            }
        }
    }
}
```

## Event registration

The Common module maps events to notification listeners here:

```php
protected $listen = [
    StudentEvaluated::class => [
        SendEvaluationNotification::class,
    ],
    EvaluationUpdated::class => [
        SendEvaluationNotification::class,
    ],
    AttendanceRecorded::class => [
        SendAttendanceNotification::class,
    ],
    StudentApproved::class => [
        SendStudentStatusNotification::class,
    ],
    StudentRejected::class => [
        SendStudentStatusNotification::class,
    ],
];
```

## Best practices

- Use a listener or service when an event is important to users.
- Keep message text clear and actionable.
- Use `type` for client-side routing or filtering.
- Persist all notifications in the database so the app can show history.
- Send FCM only when `fcm_token` exists.

## Required setup for Claude code

If you are generating code with Claude, use the exact service and model names shown above.

- `Modules\Common\Services\NotificationService`
- `Modules\Common\Models\Notification`
- `Modules\Common\Listeners\SendAttendanceNotification`
- `Modules\Common\Providers\EventServiceProvider`

These are the pieces Claude must stitch together to use Common module notifications correctly.
