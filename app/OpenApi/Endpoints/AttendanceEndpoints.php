<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class AttendanceEndpoints
{
    #[OA\Get(
        path: '/education/attendance',
        operationId: 'getAttendanceList',
        tags: ['Attendance'],
        summary: 'جلب سجل الحضور والغياب',
        description: 'يسمح للمعلم برؤية حضور حلقته، ولولي الأمر برؤية حضور أبنائه، وللمشرف برؤية حضور مسجده.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'halaqa_id', in: 'query', description: 'فلترة حسب الحلقة', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'student_id', in: 'query', description: 'فلترة حسب الطالب', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', description: 'فلترة حسب التاريخ (YYYY-MM-DD)', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page', in: 'query', description: 'رقم الصفحة للترقيم', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة الحضور',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب سجل الحضور بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(
                                        property: 'student',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 10),
                                            new OA\Property(property: 'name', type: 'string', example: 'محمد أحمد')
                                        ]
                                    ),
                                    new OA\Property(
                                        property: 'halaqa',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 5),
                                            new OA\Property(property: 'name', type: 'string', example: 'حلقة البخاري')
                                        ]
                                    ),
                                    new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-05-04'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['present', 'absent', 'late'], example: 'present'),
                                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'وصل متأخراً 10 دقائق'),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2026-05-04 08:00:00')
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح - التوكن مفقود')
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/education/attendance',
        operationId: 'storeBulkAttendance',
        tags: ['Attendance'],
        summary: 'تسجيل الحضور والغياب (جماعي)',
        description: 'يستخدم من قبل المعلم فقط لتسجيل حضور طلاب حلقته في يوم محدد. يدعم التحديث التلقائي في حال تكرار الإرسال.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['halaqa_id', 'date', 'attendances'],
                properties: [
                    new OA\Property(property: 'halaqa_id', type: 'integer', example: 5),
                    new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-05-04'),
                    new OA\Property(
                        property: 'attendances',
                        type: 'array',
                        items: new OA\Items(
                            required: ['student_id', 'status'],
                            properties: [
                                new OA\Property(property: 'student_id', type: 'integer', example: 10),
                                new OA\Property(
                                    property: 'status',
                                    type: 'string',
                                    enum: ['present', 'absent', 'late', 'absent_with_excuse'],
                                    example: 'present',
                                ),
                                new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'ملاحظة اختيارية')
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم التسجيل بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تسجيل الحضور بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(),
                            example: []
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            example: null
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'خطأ في التحقق (مثلاً: طلاب لا ينتمون للحلقة)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'بعض الطلاب غير تابعين لهذه الحلقة'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            )
        ]
    )]
    public function storeBulk() {}
}
