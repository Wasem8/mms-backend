<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class MosqueSpaceEndpoints
{
    // ─────────────────────────────────────────────
    //  GET SPACES BY MOSQUE
    // ─────────────────────────────────────────────
    #[OA\Get(
        path: '/mosques/{mosque}/spaces',
        operationId: 'getMosqueSpaces',
        tags: ['Spaces'],
        summary: 'List mosque spaces',
        description: 'Retrieve all spaces belonging to a specific mosque.',
        parameters: [
            new OA\Parameter(
                name: 'mosque',
                in: 'path',
                required: true,
                description: 'Mosque ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Spaces retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب مساحات المسجد بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'قاعة الصلاة الرئيسية'),
                                    new OA\Property(property: 'capacity', type: 'integer', example: 200),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    public function index() {}

    // ─────────────────────────────────────────────
    //  SHOW SPACE
    // ─────────────────────────────────────────────
    #[OA\Get(
        path: '/mosques/{mosque}/spaces/{space}',
        operationId: 'getSingleSpace',
        tags: ['Spaces'],
        summary: 'Get single space',
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'space', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Space retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب تفاصيل المساحة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'mosque_id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'capacity', type: 'integer'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found')
        ]
    )]
    public function show() {}

    // ─────────────────────────────────────────────
    //  CREATE SPACE
    // ─────────────────────────────────────────────
    #[OA\Post(
        path: '/mosques/{mosque}/spaces',
        operationId: 'createSpace',
        tags: ['Spaces'],
        summary: 'Create new space',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'capacity'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'قاعة النساء'),
                    new OA\Property(property: 'capacity', type: 'integer', example: 100),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Space created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تمت إضافة المساحة بنجاح'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    public function store() {}

    // ─────────────────────────────────────────────
    //  UPDATE SPACE
    // ─────────────────────────────────────────────
    #[OA\Put(
        path: '/mosques/{mosque}/spaces/{space}',
        operationId: 'updateSpace',
        tags: ['Spaces'],
        summary: 'Update space',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'space', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'قاعة كبار السن'),
                    new OA\Property(property: 'capacity', type: 'integer', example: 80),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Space updated successfully'
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function update() {}

    // ─────────────────────────────────────────────
    //  DELETE SPACE
    // ─────────────────────────────────────────────
    #[OA\Delete(
        path: '/mosques/{mosque}/spaces/{space}',
        operationId: 'deleteSpace',
        tags: ['Spaces'],
        summary: 'Delete space',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'space', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Space deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف المساحة بنجاح')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found')
        ]
    )]
    public function destroy() {}
}
