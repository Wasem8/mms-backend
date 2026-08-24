<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class TeacherEndpoints
{
    const TAG_NAME = 'Teachers Management';
    const ROLE_REQUIRED = 'مشرف الحلقات / مدير المسجد';

    #[OA\Get(
        path: '/education/teachers',
        operationId: 'getTeachersList',
        tags: [self::TAG_NAME],
        summary: 'قائمة المعلمين: ' . self::ROLE_REQUIRED,
        description: 'تعيد القائمة بناءً على الدور مع دعم البحث والفلترة والبيجينيشن.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            // 🎯 الفلاتر الجديدة المضافة للـ OpenAPI
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'تصفية حسب حالة المعلم (active, paused, suspended)',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['active', 'paused', 'suspended'])
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'البحث باسم المعلم، البريد الإلكتروني، أو رقم الهاتف',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'عدد العناصر في الصفحة الواحدة (الافتراضي 10)',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'رقم الصفحة المراد جلبها',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة المعلمين المسترجعة بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب قائمة المعلمين.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 4),
                                    new OA\Property(property: 'name', type: 'string', example: 'الشيخ عبد الرحمن السديس'),
                                    new OA\Property(property: 'email', type: 'string', example: 'teacher@test.com'),
                                    new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'phone', type: 'string', example: '+966500000000', nullable: true),
                                    new OA\Property(property: 'specialization', type: 'string', example: 'عاصم عن حفص والتجويد المتقدم', nullable: true),
                                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    new OA\Property(property: 'notes', type: 'string', example: 'تم نقل المعلم لحلقات المتقدمين لكفاءته العالية.', nullable: true),
                                    // 🎯 إضافة الحقول المستحدثة للقائمة
                                    new OA\Property(property: 'halaqats_count', type: 'integer', example: 1),
                                    new OA\Property(property: 'students_count', type: 'integer', example: 15),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2026-07-26 08:22:33'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/Pagination')
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'خطأ في التحقق من الفلاتر الممررة (Validation Error)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'قيمة الفلتر الممررة غير صحيحة.'),
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/education/teachers/{id}',
        operationId: 'getTeacherDetails',
        tags: [self::TAG_NAME],
        summary: 'تفاصيل المعلم العميقة: ' . self::ROLE_REQUIRED,
        description: 'جلب بيانات المعلم، بروفايله الخاص، الحلقات المسندة إليه، والإحصائيات الإدارية المسجلة عنه.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', description: 'معرف المعلم (User ID)', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تفاصيل المعلم والحلقات المسترجعة',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب تفاصيل المعلم'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 4),
                                new OA\Property(property: 'name', type: 'string', example: 'الشيخ عبد الرحمن السديس'),
                                new OA\Property(property: 'email', type: 'string', example: 'teacher@test.com'),
                                new OA\Property(property: 'phone', type: 'string', example: '+966500000000', nullable: true),
                                new OA\Property(property: 'specialization', type: 'string', example: 'عاصم عن حفص والتجويد المتقدم', nullable: true),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'notes', type: 'string', example: 'تم نقل المعلم لحلقات المتقدمين لكفاءته العالية.', nullable: true),
                                new OA\Property(
                                    property: 'halaqats',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: 'حلقة التميز - الشيخ عبد الرحمن السديس'),
                                            new OA\Property(
                                                property: 'stats',
                                                type: 'object',
                                                properties: [
                                                    new OA\Property(property: 'total_students', type: 'integer', example: 15),
                                                    new OA\Property(property: 'total_present_all_time', type: 'integer', example: 117),
                                                    new OA\Property(property: 'total_absent_all_time', type: 'integer', example: 46),
                                                    // 🎯 تحديث النوع ليكون رقمياً مجرداً بدلاً من نص ينتهي بـ %
                                                    new OA\Property(property: 'overall_attendance_rate', type: 'number', format: 'float', example: 71.78)
                                                ]
                                            )
                                        ]
                                    )
                                ),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-07-26 08:22:33'),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function show($id) {}

    #[OA\Put(
        path: '/education/teachers/{id}',
        operationId: 'updateTeacher',
        tags: [self::TAG_NAME],
        summary: 'تعديل بيانات المعلم وتغيير حالته: ' . self::ROLE_REQUIRED,
        description: 'تعديل الاسم (في جدول المستخدمين) وتحديث بيانات البروفايل المنفصل (تخصص، ملاحظات، هاتف)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', description: 'معرف المعلم (User ID) المراد تعديله', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', description: 'اسم المعلم كاملاً', example: 'الشيخ عبد الرحمن السديس'),
                    new OA\Property(property: 'phone', type: 'string', description: 'رقم الهاتف الجوال للبروفايل', example: '+966500000000'),
                    new OA\Property(property: 'specialization', type: 'string', description: 'مجال التخصص والتدريس المعين له', example: 'عاصم عن حفص والتجويد المتقدم'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'paused', 'suspended'], description: 'حالة المعلم الوظيفية في البروفايل', example: 'active'),
                    new OA\Property(property: 'notes', type: 'string', description: 'ملاحظات المشرف الإدارية', example: 'تم نقل المعلم لحلقات المتقدمين لكفاءته العالية.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم تحديث بيانات وبروفايل المعلم بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث بيانات المعلم بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 4),
                                new OA\Property(property: 'name', type: 'string', example: 'الشيخ عبد الرحمن السديس'),
                                new OA\Property(property: 'email', type: 'string', example: 'teacher@test.com'),
                                new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                                new OA\Property(property: 'phone', type: 'string', example: '+966500000000', nullable: true),
                                new OA\Property(property: 'specialization', type: 'string', example: 'عاصم عن حفص والتجويد المتقدم', nullable: true),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'notes', type: 'string', example: 'تم نقل المعلم لحلقات المتقدمين لكفاءته العالية.', nullable: true),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-07-26 08:22:33'),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(
                response: 422,
                description: 'خطأ في التحقق من صحة المدخلات (Validation Error)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حالة المعلم الممررة غير صحيحة.'),
                        new OA\Property(property: 'errors', type: 'object', nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function update($id) {}
}
