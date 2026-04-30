<?php

namespace App\OpenApi\Endpoints;

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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'capacity'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'حلقة التجويد'),
                    new OA\Property(property: 'teacher_id', type: 'integer', nullable: true, example: 4),
                    new OA\Property(property: 'capacity', type: 'integer', example: 20),
                    new OA\Property(property: 'schedule_days', type: 'array', items: new OA\Items(type: 'string'), example: ['Sunday', 'Tuesday']),
                    new OA\Property(property: 'start_time', type: 'string', example: '16:00'),
                    new OA\Property(property: 'end_time', type: 'string', example: '18:00'),
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
        summary: 'Add students to halaqa: ' . self::ROLE_REQUIRED,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'students', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3])
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Students added successfully'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function attachStudents() {}

    #[OA\Delete(
        path: '/education/halaqat/{id}/students/{studentId}',
        operationId: 'detachStudent',
        tags: ['Education'],
        summary: 'Remove student from halaqa: ' . self::ROLE_REQUIRED,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'studentId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Student removed successfully'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function detachStudent() {}
}
