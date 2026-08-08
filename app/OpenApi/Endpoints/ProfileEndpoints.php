<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class ProfileEndpoints
{
    #[OA\Get(
        path: '/profile',
        operationId: 'getProfile',
        tags: ['Profile'],
        summary: 'Get authenticated user profile',
        description: 'Returns the personal profile info of the currently authenticated user (name, email, phone, role, linked mosque if any).',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب البيانات بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'first_name', type: 'string', example: 'أحمد'),
                                new OA\Property(property: 'last_name', type: 'string', example: 'الشديد'),
                                new OA\Property(property: 'full_name', type: 'string', example: 'أحمد الشديد'),
                                new OA\Property(property: 'email', type: 'string', example: 'manager@test.com'),
                                new OA\Property(property: 'phone', type: 'string', example: '0559876543'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'role', type: 'string', example: 'mosque_manager'),
                                new OA\Property(
                                    property: 'mosque',
                                    type: 'object',
                                    nullable: true,
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 2),
                                        new OA\Property(property: 'name', type: 'string', example: 'مسجد الرحمة الجامع'),
                                    ]
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/profile',
        operationId: 'updateProfile',
        tags: ['Profile'],
        summary: 'Update authenticated user profile',
        description: 'Updates the editable personal fields of the authenticated user: full name, email, and phone. Other fields (status, role, mosque) are read-only from this endpoint.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'email', 'phone'],
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', example: 'أحمد الشديد', maxLength: 150),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'manager@test.com', maxLength: 150),
                    new OA\Property(property: 'phone', type: 'string', example: '0559876543', maxLength: 20),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث البيانات بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'first_name', type: 'string', example: 'أحمد'),
                                new OA\Property(property: 'last_name', type: 'string', example: 'الشديد'),
                                new OA\Property(property: 'full_name', type: 'string', example: 'أحمد الشديد'),
                                new OA\Property(property: 'email', type: 'string', example: 'manager@test.com'),
                                new OA\Property(property: 'phone', type: 'string', example: '0559876543'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'role', type: 'string', example: 'mosque_manager'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error - Request validation failed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: ['email' => ['The email has already been taken.']]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function update() {}
}
