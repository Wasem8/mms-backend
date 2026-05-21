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
        description: 'تعيد القائمة بناءً على الدور: مدير المنطقة يرى الكل، مدير المسجد والمشرف يريان معلمي مسجدهما فقط.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة المعلمين المسترجعة بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب قائمة المعلمين بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 5),
                                    new OA\Property(property: 'name', type: 'string', example: 'الشيخ عبد الرحمن السديس'),
                                    new OA\Property(property: 'email', type: 'string', example: 'teacher1@mosque.com'),
                                    new OA\Property(property: 'mosque_id', type: 'integer', example: 1), // ✅ تم إضافته ليتوافق مع TeacherResource
                                    new OA\Property(property: 'phone', type: 'string', example: '+966500000000', nullable: true),
                                    new OA\Property(property: 'specialization', type: 'string', example: 'التجويد والقراءات العشر', nullable: true),
                                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    new OA\Property(property: 'notes', type: 'string', example: null, nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2026-05-14 07:48:51'), // ✅ تم إضافته ليتوافق مع TeacherResource
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/Pagination')
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
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب تفاصيل المعلم بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 5),
                                new OA\Property(property: 'name', type: 'string', example: 'الشيخ عبد الرحمن السديس'),
                                new OA\Property(property: 'email', type: 'string', example: 'teacher1@mosque.com'),
                                new OA\Property(property: 'phone', type: 'string', example: '+966500000000', nullable: true),
                                new OA\Property(property: 'specialization', type: 'string', example: 'التجويد والقراءات العشر', nullable: true),
                                new OA\Property(property: 'status', type: 'string', example: 'paused'),
                                new OA\Property(property: 'notes', type: 'string', example: 'تم الإيقاف المؤقت لظروف السفر الطارئة.', nullable: true),
                                new OA\Property(
                                    property: 'halaqats',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 12),
                                            new OA\Property(property: 'name', type: 'string', example: 'حلقة الإتقان والتميز'),
                                            // ✅ تم تحديث هيكل الإحصائيات ليتطابق حرفياً مع TeacherDetailResource
                                            new OA\Property(
                                                property: 'stats',
                                                type: 'object',
                                                properties: [
                                                    new OA\Property(property: 'total_students', type: 'integer', example: 15),
                                                    new OA\Property(property: 'total_present_all_time', type: 'integer', example: 120),
                                                    new OA\Property(property: 'total_absent_all_time', type: 'integer', example: 5),
                                                    new OA\Property(property: 'overall_attendance_rate', type: 'string', example: '96%')
                                                ]
                                            )
                                        ]
                                    )
                                ),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-05-14 07:48:51'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
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
        description: 'تعديل الاسم (في جدول المستخدمين) وتحديث بيانات البروفايل المنفصل (تخصص، ملاحظات، هاتف) ',
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
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث بيانات المعلم وحالته بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 5),
                                new OA\Property(property: 'name', type: 'string', example: 'الشيخ عبد الرحمن السديس'),
                                new OA\Property(property: 'email', type: 'string', example: 'teacher1@mosque.com'),
                                new OA\Property(property: 'mosque_id', type: 'integer', example: 1), // ✅ أضيفت لتطابق TeacherResource المستخدم في الـ update
                                new OA\Property(property: 'phone', type: 'string', example: '+966500000000', nullable: true),
                                new OA\Property(property: 'specialization', type: 'string', example: 'عاصم عن حفص والتجويد المتقدم', nullable: true),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'notes', type: 'string', example: 'تم نقل المعلم لحلقات المتقدمين لكفاءته العالية.', nullable: true),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-05-14 07:48:51'), // ✅ أضيفت لتطابق TeacherResource
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
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
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function update($id) {}
}
