<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class InvitationEndpoints
{
    private const AVAILABLE_ROLES = ['super_admin', 'mosque_manager', 'halaqa_supervisor', 'teacher', 'parent'];

    #[OA\Post(
        path: '/invitations/send',
        operationId: 'sendInvitation',
        tags: ['Invitations'],
        summary: 'Send invitation to user',
        description: 'Send invitation email. Prevents duplicate active invitations for same email.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'role'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@test.com'),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: self::AVAILABLE_ROLES,
                        example: 'teacher',
                        description: 'User role to assign'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Invitation sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Invitation sent successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'teacher'),
                                new OA\Property(property: 'token', type: 'string', example: 'abc123token'),
                                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'accepted', 'rejected']),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated - Missing or invalid token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
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
                            example: ['field_name' => ['Error message']]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function sendInvitation() {}

    #[OA\Post(
        path: '/invitations/accept',
        operationId: 'acceptInvitation',
        tags: ['Invitations'],
        summary: 'Accept invitation',
        description: 'Accept invitation using token. Creates new account or attaches role to existing user.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token'],
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'abc123token'),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Waseem',
                        description: 'Required only if creating new account'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'password123',
                        description: 'Required only if creating new account'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        example: 'password123',
                        description: 'Required only if creating new account'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Invitation accepted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Invitation accepted successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'blocked'], example: 'active'),
                                        new OA\Property(
                                            property: 'roles',
                                            type: 'array',
                                            items: new OA\Items(type: 'string'),
                                            example: ['teacher']
                                        ),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'user_status',
                                    type: 'string',
                                    enum: ['new', 'existing'],
                                    example: 'new'
                                ),
                                new OA\Property(
                                    property: 'roles_added',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['teacher']
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
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
                            example: ['field_name' => ['Error message']]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function acceptInvitation() {}
}
