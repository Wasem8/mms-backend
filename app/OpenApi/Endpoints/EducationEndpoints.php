<?php

namespace App\OpenApi\Endpoints;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class EducationEndpoints
{
    private const HALAQA_STATUSES = ['active', 'inactive'];
    private const ROLE_REQUIRED = 'Required Role: **halaqa_supervisor**';

    #[OA\Get(
        path: '/education/halaqat',
        operationId: 'getHalaqat',
        tags: ['Education'],
        summary: 'List all halaqat: ' . self::ROLE_REQUIRED,
        description: 'Get paginated list of halaqat. ' ,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Halaqat retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب الحلقات بنجاح'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/HalaqaResource')),
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/Pagination'),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function getHalaqat() {}

    #[OA\Get(
        path: '/education/halaqat/{id}',
        operationId: 'showHalaqa',
        tags: ['Education'],
        summary: 'Get halaqa details' . self::ROLE_REQUIRED,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Halaqa details retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب تفاصيل الحلقة'),
                        new OA\Property(
                            property: 'data',
                            allOf: [
                                new OA\Schema(ref: '#/components/schemas/HalaqaResource'), // جلب الخصائص الأساسية
                                new OA\Schema(
                                    properties: [
                                        new OA\Property(
                                            property: 'students',
                                            type: 'array',
                                            items: new OA\Items(
                                                properties: [
                                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                                    new OA\Property(property: 'first_name', type: 'string', example: 'Student'),
                                                    new OA\Property(property: 'last_name', type: 'string', example: '1'),
                                                    new OA\Property(property: 'parent_name', type: 'string', nullable: true, example: null),
                                                    new OA\Property(property: 'email', type: 'string', example: 'student1@test.com'),
                                                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: null),
                                                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                                                ]
                                            )
                                        )
                                    ]
                                )
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function showHalaqa() {}

    #[OA\Post(
        path: '/education/halaqat',
        operationId: 'storeHalaqa',
        tags: ['Education'],
        summary: 'Create new halaqa: ' . self::ROLE_REQUIRED,
        security: [['bearerAuth' => []]],
parameters: [
new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
],
requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'capacity'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'حلقة التجويد'),
                    new OA\Property(property: 'teacher_id', type: 'integer', nullable: true, example: 4),
                    new OA\Property(property: 'capacity', type: 'integer', example: 20),
                    new OA\Property(property: 'schedule_days', type: 'array', items: new OA\Items(type: 'string'), example: ['sunday', 'tuesday', 'thursday']),
                    new OA\Property(property: 'start_time', type: 'string', format: 'time', example: '16:00:00'),
                    new OA\Property(property: 'end_time', type: 'string', format: 'time', example: '18:00:00'),
                    new OA\Property(property: 'status', type: 'string', enum: self::HALAQA_STATUSES, example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Halaqa created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء الحلقة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/HalaqaResource' // استخدام المرجع الذي يحتوي على التفاصيل
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function storeHalaqa() {}

    #[OA\Put(
        path: '/education/halaqat/{id}',
        operationId: 'updateHalaqa',
        tags: ['Education'],
        summary: 'Update halaqa: ' . self::ROLE_REQUIRED,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID of the halaqa to update',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'حلقة التجويد المحدثة'),
                    new OA\Property(property: 'teacher_id', type: 'integer', nullable: true, example: 5),
                    new OA\Property(property: 'capacity', type: 'integer', example: 25),
                    new OA\Property(
                        property: 'schedule_days',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['Monday', 'Wednesday']
                    ),
                    new OA\Property(property: 'start_time', type: 'string', example: '15:30'),
                    new OA\Property(property: 'end_time', type: 'string', example: '17:30'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Halaqa updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث بيانات الحلقة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/HalaqaResource'
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function updateHalaqa() {}

    #[OA\Delete(
        path: '/education/halaqat/{id}',
        operationId: 'deleteHalaqa',
        tags: ['Education'],
        summary: 'Delete halaqa: ' . self::ROLE_REQUIRED,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Halaqa deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف الحلقة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(),
                            example: []
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function deleteHalaqa() {}

    #[OA\Post(
        path: '/education/halaqat/{id}/students',
        operationId: 'attachStudents',
        tags: ['Education'],
        summary: 'إضافة طلاب إلى الحلقة: ' . self::ROLE_REQUIRED,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف الحلقة',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['students'],
                properties: [
                    new OA\Property(
                        property: 'students',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 5, 12]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تمت العملية بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إضافة الطلاب إلى الحلقة بنجاح'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(), example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'فشل التحقق (طلاب مفقودين، تبعية مسجد مختلف، أو تجاوز سعة)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'students',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ["الطالب (أحمد) يتبع لمسجد آخر.", "المعرفات التالية غير موجودة في النظام: 12"]
                                ),
                                new OA\Property(
                                    property: 'capacity',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ["عذراً، الحلقة لا تستوعب هذا العدد. المقاعد المتبقية: 3"]
                                )
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function attachStudents()
    {

    }

    #[OA\Delete(
        path: '/education/halaqat/{id}/students/{studentId}',
        operationId: 'detachStudent',
        tags: ['Education'],
        summary: 'إزالة طالب من الحلقة: ' . self::ROLE_REQUIRED,
        security: [['bearerAuth' => []]],
        parameters:[
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', description: 'معرف الحلقة', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'studentId', in: 'path', description: 'معرف الطالب', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تمت الإزالة بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إزالة الطالب من الحلقة'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(), example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, description: 'الطالب غير موجود في هذه الحلقة',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'هذا الطالب غير مسجل في هذه الحلقة.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                ))
        ]
    )]
    public function detachStudent() {}

    #[OA\Post(
        path: '/education/sync',
        operationId: 'syncOfflineOperations',
        tags: ['Education', 'Offline Sync'],
        summary: 'Offline batch synchronization',
        description: 'Synchronize offline teacher operations (attendance, evaluation, excuse decision). Each operation is processed independently and returns its own result.',
        security: [['bearerAuth' => []]],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['ops'],
                properties: [

                    new OA\Property(
                        property: 'ops',
                        type: 'array',
                        description: 'List of offline operations queued on the device',
                        items: new OA\Items(
                            type: 'object',
                            required: ['type', 'client_uuid', 'data'],
                            properties: [

                                new OA\Property(
                                    property: 'type',
                                    type: 'string',
                                    enum: ['attendance', 'evaluation', 'excuse_decision'],
                                    example: 'evaluation'
                                ),

                                new OA\Property(
                                    property: 'client_uuid',
                                    type: 'string',
                                    format: 'uuid',
                                    description: 'Idempotency key generated by the client',
                                    example: '6b2d1c44-9c2f-4f3a-8a0b-123456789abc'
                                ),

                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    additionalProperties: true,
                                    description: 'Payload depends on operation type'
                                )
                            ]
                        ),
                        example: [

                            [
                                'type' => 'attendance',
                                'client_uuid' => '3fa85f64-5717-4562-b3fc-2c963f66afa6',
                                'data' => [
                                    'halaqa_id' => 1,
                                    'date' => '2026-06-05',
                                    'attendances' => [
                                        [
                                            'student_id' => 12,
                                            'status' => 'present'
                                        ],
                                        [
                                            'student_id' => 13,
                                            'status' => 'absent'
                                        ]
                                    ]
                                ]
                            ],

                            [
                                'type' => 'evaluation',
                                'client_uuid' => '6b2d1c44-9c2f-4f3a-8a0b-123456789abc',
                                'data' => [
                                    'halaqa_id' => 1,
                                    'student_id' => 12,
                                    'surah_name' => 'الفاتحة',
                                    'from_ayah' => 1,
                                    'to_ayah' => 7,
                                    'score' => 95,
                                    'evaluated_at' => '2026-06-05T15:30:00Z'
                                ]
                            ],

                            [
                                'type' => 'excuse_decision',
                                'client_uuid' => '9a7d2f11-2222-4aaa-bbbb-ccccdddd1111',
                                'data' => [
                                    'excuse_id' => 10,
                                    'status' => 'accepted',
                                    'admin_comment' => 'مقبول'
                                ]
                            ]
                        ]
                    )
                ]
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'Synchronization completed',
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
                            example: 'تمت مزامنة العمليات بنجاح'
                        ),

                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [

                                    new OA\Property(
                                        property: 'client_uuid',
                                        type: 'string',
                                        example: '6b2d1c44-9c2f-4f3a-8a0b-123456789abc'
                                    ),

                                    new OA\Property(
                                        property: 'status',
                                        type: 'string',
                                        enum: ['ok', 'error', 'conflict'],
                                        example: 'ok'
                                    ),

                                    new OA\Property(
                                        property: 'message',
                                        type: 'string',
                                        example: 'evaluation saved'
                                    ),

                                    new OA\Property(
                                        property: 'data',
                                        type: 'object',
                                        nullable: true
                                    )
                                ]
                            )
                        )
                    ]
                )
            ),

            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            )
        ]
    )]
    public function syncOffline() {}
}

