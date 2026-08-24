<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class InvitationEndpoints
{
    private const AVAILABLE_ROLES = ['super_admin', 'mosque_manager', 'halaqa_supervisor', 'teacher', 'parent'];


    #[OA\Get(
        path: '/invitations',
        operationId: 'listInvitations',
        tags: ['Invitations'],
        summary: 'Get list of invitations',
        description: 'Fetch paginated invitations for the current mosque. Supports optional filtering by invitation status.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter invitations by status (pending, accepted, expired)',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['pending', 'accepted', 'expired']
                )
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number for pagination',
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Invitations fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب الدعوات بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'email', type: 'string', example: 'teacher@example.com'),
                                    new OA\Property(property: 'role', type: 'string', example: 'teacher'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'accepted', 'expired'], example: 'pending'),
                                    new OA\Property(property: 'status_label', type: 'string', example: 'معلقة'),
                                    new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'accepted_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(
                                        property: 'mosque',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 5),
                                            new OA\Property(property: 'name', type: 'string', example: 'جامع القبلتين')
                                        ]
                                    )
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            )
        ]
    )]
    public function listInvitations() {}

    #[OA\Post(
        path: '/invitations/send',
        operationId: 'sendInvitation',
        tags: ['Invitations'],
        summary: 'Send invitation to user',
        description: "Send invitation email based on a strict hierarchy:\n\n" .
        "1. **Super Admin** (Zone Manager): Can ONLY invite a `mosque_manager`. (Requires `mosque_id` in request)\n" .
        "2. **Mosque Manager**: Can invite a `halaqa_supervisor` or `teacher`.\n" .
        "3. **Halaqa Supervisor**: Can ONLY invite a `teacher`.\n\n" .
        "**Business Rules:** Each mosque is strictly limited to ONE active/pending Mosque Manager and ONE active/pending Halaqa Supervisor.",
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
                        enum: ['mosque_manager', 'halaqa_supervisor', 'teacher'], // 🎯 توثيق الأدوار المتاحة للدعوة صراحة هنا
                        example: 'mosque_manager',
                        description: 'The role assigned to the invited user. Allowed values: mosque_manager, halaqa_supervisor, teacher.'
                    ),
                    new OA\Property(
                        property: 'mosque_id',
                        type: 'integer',
                        nullable: true,
                        example: 5,
                        description: 'Required ONLY if the sender is a Super Admin (Zone Manager). For other roles, it is handled automatically from their profile.'
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
                                new OA\Property(property: 'role', type: 'string', example: 'mosque_manager'),
                                new OA\Property(property: 'mosque_id', type: 'integer', example: 5),
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
                description: 'Validation error - Request validation failed or hierarchy/limit violation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: ['role' => ['لا يمكن إرسال الدعوة. هذا المسجد يمتلك (مدير مسجد) نشط بالفعل.']]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function sendInvitation() {}

    #[OA\Post(
        path: '/invitations/{id}/resend',
        operationId: 'resendInvitation',
        tags: ['Invitations'],
        summary: 'Resend an existing invitation',
        description: 'Regenerates token, extends expiration time by 7 days, and resends the notification email for an unaccepted invitation.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Invitation ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Invitation resent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تمت إعادة إرسال الدعوة بنجاح وتمديد صلاحيتها.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'email', type: 'string', example: 'teacher@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'teacher'),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'status_label', type: 'string', example: 'معلقة'),
                                new OA\Property(property: 'expires_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized to resend this invitation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'غير مصرح لك بإعادة إرسال هذه الدعوة'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation Error - E.g. Cannot resend an already accepted invitation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: ['invitation' => ['لا يمكن إعادة إرسال دعوة مقبولة بالفعل.']]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            )
        ]
    )]
    public function resendInvitation() {}

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


    #[OA\Delete(
        path: '/invitations/{id}',
        operationId: 'deleteInvitation',
        tags: ['Invitations'],
        summary: 'Delete an invitation',
        description: 'Delete an invitation created by the currently authenticated user. Accepted invitations cannot be deleted.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Invitation ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Invitation deleted successfully',
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
                            example: 'تم حذف الدعوة بنجاح.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            nullable: true,
                            example: null
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
                response: 401,
                description: 'Unauthenticated - Missing or invalid token',
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
                            example: 'غير مصرح لك بالوصول'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            nullable: true,
                            example: null
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
                response: 403,
                description: 'Forbidden - User is not the creator of the invitation',
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
                            example: 'غير مصرح لك بحذف هذه الدعوة. يمكنك حذف الدعوات التي قمت بإنشائها فقط.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            nullable: true,
                            example: null
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
                response: 404,
                description: 'Invitation not found',
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
                            example: 'Invitation not found.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            nullable: true,
                            example: null
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
                description: 'Cannot delete an accepted invitation',
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
                            example: 'لا يمكن حذف الدعوة لأن المستخدم قام بقبولها بالفعل.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            nullable: true,
                            example: null
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
        ]
    )]
    public function deleteInvitation() {}
}
