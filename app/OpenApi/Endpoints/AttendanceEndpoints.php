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
        description: 'يسجل حضور الطلاب بشكل جماعي. إذا احتوى الطلب على طلاب غير موجودين أو لم يعودوا تابعين للحلقة فسيتم تجاهلهم وإرجاع معرفاتهم داخل skipped_student_ids دون فشل العملية بالكامل.',
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
                description: 'تم تسجيل الحضور بنجاح مع إمكانية تخطي بعض الطلاب غير الصالحين',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم تسجيل الحضور بنجاح'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'saved_count',
                                    type: 'integer',
                                    example: 25,
                                    description: 'عدد سجلات الحضور التي تم حفظها'
                                ),
                                new OA\Property(
                                    property: 'skipped_student_ids',
                                    type: 'array',
                                    description: 'الطلاب الذين تم تجاهلهم لأنهم لم يعودوا ضمن الحلقة أو غير موجودين',
                                    items: new OA\Items(type: 'integer'),
                                    example: [18, 22]
                                )
                            ]
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            example: null
                        )
                    ]
                )
            ),

            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                ref: '#/components/responses/Forbidden'
            ),

            new OA\Response(
                response: 422,
                description: 'خطأ في البيانات المرسلة',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'boolean',
                            example: false
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Validation error.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: [
                                'date' => [
                                    'The date field is required.'
                                ]
                            ]
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            example: null
                        )
                    ]
                )
            )
        ]
    )]
    public function storeBulk() {}
}
