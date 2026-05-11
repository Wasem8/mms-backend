<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class ExcuseEndpoints
{
    #[OA\Post(
        path: '/education/attendance/excuses',
        operationId: 'storeAttendanceExcuse',
        tags: ['Attendance Excuses'],
        summary: 'إرسال عذر غياب جديد',
        description: 'يسمح لولي الأمر بإرسال عذر غياب لأحد أبنائه. النظام يمنع إرسال أعذار لتواريخ قديمة.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['student_id', 'halaqa_id', 'absence_date', 'reason'],
                properties: [
                    new OA\Property(property: 'student_id', type: 'integer', example: 10, description: 'معرف الطالب (يجب أن يكون من أبناء ولي الأمر)'),
                    new OA\Property(property: 'halaqa_id', type: 'integer', example: 5, description: 'معرف الحلقة المسجل بها الطالب'),
                    new OA\Property(property: 'absence_date', type: 'string', format: 'date', example: '2026-05-10', description: 'تاريخ الغياب (YYYY-MM-DD)'),
                    new OA\Property(property: 'reason', type: 'string', example: 'وعكة صحية مفاجئة تستدعي الراحة', minLength: 10),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم إرسال العذر بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إرسال عذر الغياب بنجاح، وسيتم إبلاغ المعلم.'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'خطأ في التحقق من البيانات (مثلاً التاريخ في الماضي أو الطالب ليس ابنه)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'لا يمكن تقديم عذر لتاريخ في الماضي.'),
                        new OA\Property(property: 'data', type: 'object', example: ["absence_date" => ["لا يمكن تقديم عذر لتاريخ في الماضي."]])
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح (التوكن منتهي أو مفقود)')
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/education/my-excuses',
        operationId: 'getMyExcuses',
        tags: ['Attendance Excuses'],
        summary: 'عرض قائمة الأعذار الخاصة بي',
        description: 'يسمح لولي الأمر بمتابعة حالة الأعذار التي أرسلها (مقبولة، مرفوضة، أو قيد الانتظار).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة الأعذار',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب البيانات بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(
                                        property: 'student',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 10),
                                            new OA\Property(property: 'name', type: 'string', example: 'خالد أحمد')
                                        ]
                                    ),
                                    new OA\Property(property: 'absence_date', type: 'string', format: 'date', example: '2026-05-10'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'accepted', 'rejected'], example: 'pending'),
                                    new OA\Property(property: 'reason', type: 'string', example: 'موعد طبي'),
                                    new OA\Property(property: 'admin_comment', type: 'string', nullable: true, example: 'تم قبول العذر، نتمنى له الشفاء.')
                                ]
                            )
                        ),
                            new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta', nullable: true)
                    ]
                )
            )
        ]
    )]
    public function myExcuses() {}

    #[OA\Get(
        path: '/education/teacher/excuses',
        operationId: 'getTeacherExcuses',
        tags: ['Attendance Excuses'],
        summary: 'عرض طلبات الأعذار للمعلم مع الفلترة',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'فلترة حسب الحالة (pending, accepted, rejected)',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'accepted', 'rejected'])
            ),
            new OA\Parameter(
                name: 'date',
                in: 'query',
                description: 'فلترة حسب تاريخ الغياب (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة الأعذار لطلاب المعلم',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب طلبات الأعذار بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'student_id', type: 'integer', example: 11),
                                    new OA\Property(property: 'halaqa_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'parent_id', type: 'integer', example: 5),
                                    new OA\Property(property: 'absence_date', type: 'string', example: '2026-05-10'),
                                    new OA\Property(property: 'reason', type: 'string', example: 'وعكة صحية مفاجئة تستدعي الراحة'),
                                    new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                    new OA\Property(property: 'admin_comment', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2026-05-04T07:33:50.000000Z'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2026-05-04T07:33:50.000000Z'),
                                    // كائن الطالب
                                    new OA\Property(
                                        property: 'student',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 11),
                                            new OA\Property(property: 'first_name', type: 'string', example: 'أحمد'),
                                            new OA\Property(property: 'last_name', type: 'string', example: 'محمد')
                                        ]
                                    ),
                                    // كائن الحلقة
                                    new OA\Property(
                                        property: 'halaqa',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: 'حلقة التحفيظ - المستوى الأول')
                                        ]
                                    ),
                                ]
                            )
                        ),

                        new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta')
                    ]
                )
            )
        ]
    )]
    public function indexForTeacher() {}

    #[OA\Put(
        path: '/education/teacher/excuses/{id}/process',
        operationId: 'processExcuse',
        tags: ['Attendance Excuses'],
        summary: 'معالجة عذر الغياب (قبول/رفض)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف طلب العذر',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        description: 'الحالة الجديدة للعذر',
                        type: 'string',
                        enum: ['accepted', 'rejected'],
                        default: 'accepted'
                    ),
                    new OA\Property(
                        property: 'admin_comment',
                        type: 'string',
                        example: 'تم قبول العذر بسبب الظروف الصحية',
                        nullable: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم التحديث بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث حالة العذر وتحديث سجل الحضور بنجاح.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(), example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            // إضافة استجابة الخطأ 422 للحالة التي ذكرتها
            new OA\Response(
                response: 422,
                description: 'خطأ منطقي (العذر ليس في حالة الانتظار)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'لا يمكن تعديل هذا العذر لأنه تم قبوله مسبقاً.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function process() {}
}
