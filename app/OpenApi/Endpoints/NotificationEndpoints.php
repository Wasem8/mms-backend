<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class NotificationEndpoints
{
    #[OA\Get(
        path: '/common/notifications',
        operationId: 'getUserNotifications',
        tags: ['Notifications'],
        summary: 'عرض إشعارات المستخدم الحالي',
        description: 'يجلب قائمة بكافة الإشعارات الخاصة بالمستخدم (تقييمات، غياب، تنبيهات) مع دعم الترقيم.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'is_read', in: 'query', schema: new OA\Schema(type: 'boolean'), description: 'فلترة حسب الحالة (مقروء / غير مقروء)')
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب قائمة الإشعارات بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب الإشعارات بنجاح.'),
                        // مصفوفة البيانات (Data) - أصبحت الآن مصفوفة مباشرة
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 2),
                                    new OA\Property(property: 'title', type: 'string', example: 'تقييم جديد لابنك: Student'),
                                    new OA\Property(property: 'body', type: 'string', example: 'تم تقييم الطالب في سورة الفاتحة بدرجة 90'),
                                    new OA\Property(property: 'type', type: 'string', example: 'evaluation'),
                                    new OA\Property(property: 'extra_data', type: 'object', example: ["id" => "2"], nullable: true),
                                    new OA\Property(property: 'is_read', type: 'boolean', example: false),
                                    new OA\Property(property: 'read_at', type: 'string', example: null, nullable: true),
                                    new OA\Property(property: 'created_since', type: 'string', example: 'منذ 19 دقيقة'),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2026-05-14 09:17'),
                                ]
                            )
                        ),
                        // كائن الترقيم (Pagination) - منفصل حسب ApiResponse class
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 2),
                                new OA\Property(property: 'has_more_pages', type: 'boolean', example: false),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح')
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/common/notifications/{id}/read',
        operationId: 'markNotificationAsRead',
        tags: ['Notifications'],
        summary: 'تحديد إشعار كمقروء',
        description: 'تغيير حالة إشعار محدد من "غير مقروء" إلى "مقروء".',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم التحديث بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديد الإشعار كمقروء.')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'الإشعار غير موجود')
        ]
    )]
    public function markAsRead() {}

    #[OA\Post(
        path: '/common/notifications/read-all',
        operationId: 'markAllNotificationsAsRead',
        tags: ['Notifications'],
        summary: 'تحديد الكل كمقروء',
        description: 'تحويل كافة الإشعارات غير المقروءة للمستخدم الحالي إلى حالة "مقروءة" بضغطة واحدة.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم تحديث كافة الإشعارات',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديد كل الإشعارات كمقروءة.')
                    ]
                )
            )
        ]
    )]
    public function markAllAsRead() {}

    #[OA\Delete(
        path: '/common/notifications/{id}',
        operationId: 'deleteNotification',
        tags: ['Notifications'],
        summary: 'حذف إشعار',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم الحذف بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف الإشعار بنجاح.')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'الإشعار غير موجود')
        ]
    )]
    public function destroy() {}
}
