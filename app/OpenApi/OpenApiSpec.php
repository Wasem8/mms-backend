<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'MMS API Documentation',
    version: "1.0.0",
    description: 'API documentation for Mosque Management System (MMS)'
)]
#[OA\Server(
    url: '/api',
    description: 'Main API Server'
)]
#[OA\Tag(name: 'Auth', description: 'Authentication endpoints')]

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Enter your Bearer token'
)]
class OpenApiSpec
{
    /**
     * Parent Registration
     */
    #[OA\Post(
        path: '/auth/register-parent',
        operationId: 'registerParent',
        tags: ['Auth'],
        summary: 'Register a new parent account',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Ahmed Ali'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        example: 'parent@test.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'password123'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        example: 'password123'
                    ),
                ]
            )
        ),
        responses: [

            // ✅ Success
            new OA\Response(
                response: 200,
                description: 'Account created. An OTP has been sent to your email for verification.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),

                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Account created. An OTP has been sent to your email for verification.'
                        ),

                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [

                                new OA\Property(property: 'id', type: 'integer', example: 7),

                                new OA\Property(
                                    property: 'name',
                                    type: 'string',
                                    example: 'Ahmed Ali'
                                ),

                                new OA\Property(
                                    property: 'email',
                                    type: 'string',
                                    example: 'parent@test.com'
                                ),

                                new OA\Property(
                                    property: 'status',
                                    type: 'string',
                                    example: 'active'
                                ),

                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    example: '2026-04-16T18:22:02.000000Z'
                                ),

                                new OA\Property(
                                    property: 'updated_at',
                                    type: 'string',
                                    example: '2026-04-16T18:22:02.000000Z'
                                ),
                            ]
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
            // ❌ Validation error
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: [
                                'email' => ['The email has already been taken.']
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function registerParent() {}

    /**
     * Verify OTP (Activate Parent Account)
     */
    #[OA\Post(
        path: '/auth/verify-otp',
        operationId: 'verifyOtp',
        tags: ['Auth'],
        summary: 'Verify OTP and activate account',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'otp'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        example: 'parent@test.com'
                    ),
                    new OA\Property(
                        property: 'otp',
                        type: 'string',
                        example: '123456'
                    ),
                ]
            )
        ),
        responses: [

            // ✅ Success
            new OA\Response(
                response: 200,
                description: 'Account verified successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Account verified successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 7),
                                new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                new OA\Property(property: 'email', type: 'string', example: 'parent@test.com'),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-04-16T18:22:02.000000Z'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),

            // ❌ Invalid OTP
            new OA\Response(
                response: 422,
                description: 'Invalid OTP',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: [
                                'otp' => ['Invalid or expired OTP.']
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),

        ]
    )]
    public function verifyOtp() {}

    /**
     * Login (Email & Password)
     */
    #[OA\Post(
        path: '/auth/login',
        operationId: 'login',
        tags: ['Auth'],
        summary: 'Login using email and password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'admin@test.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [

            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'access_token', type: 'string', example: '13|xxxx'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'Super Admin'),
                                        new OA\Property(property: 'email', type: 'string', example: 'admin@test.com'),
                                        new OA\Property(property: 'created_at', type: 'string', example: '2026-04-13 11:28:02'),
                                        new OA\Property(
                                            property: 'roles',
                                            type: 'array',
                                            items: new OA\Items(type: 'string', example: 'super_admin')
                                        ),
                                    ]
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Account blocked or not verified',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Authorization error.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ],
                    examples: [
                        new OA\Examples(
                            example: 'email_not_verified',
                            summary: 'Email not verified',
                            value: [
                                "status" => false,
                                "message" => "EMAIL_NOT_VERIFIED",
                                "data" => null,
                                "pagination" => null
                            ]
                        ),
                        new OA\Examples(
                            example: 'account_inactive',
                            summary: 'Account inactive',
                            value: [
                                "status" => false,
                                "message" => "ACCOUNT_INACTIVE",
                                "data" => null,
                                "pagination" => null
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 429,
                description: 'Too many login attempts',
            )
        ]
    )]
    public function login() {}
    /**
     * Logout (Authenticated)
     */
    #[OA\Post(
        path: '/auth/logout',
        operationId: 'logout',
        tags: ['Auth'],
        summary: 'Logout current user',
        security: [['bearerAuth' => []]],
        responses: [

            new OA\Response(
                response: 200,
                description: 'Logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully.'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
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
    public function logout() {}

    /**
     * Forgot Password (Send OTP)
     */
    #[OA\Post(
        path: '/auth/forgot-password',
        operationId: 'forgotPassword',
        tags: ['Auth'],
        summary: 'Send OTP to email for password reset',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        example: 'admin@test.com'
                    ),
                ]
            )
        ),
        responses: [

            new OA\Response(
                response: 200,
                description: 'Request processed (OTP sent if email exists)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'If the email exists, an OTP has been sent.'
                        ),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            )

        ]
    )]
    public function forgotPassword() {}
    /**
     * Reset Password using OTP
     */
    #[OA\Post(
        path: '/auth/reset-password',
        operationId: 'resetPassword',
        tags: ['Auth'],
        summary: 'Reset password using OTP',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'otp', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'admin@test.com'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456'),
                    new OA\Property(property: 'password', type: 'string', example: 'newpassword'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'newpassword'),
                ]
            )
        ),
        responses: [

            new OA\Response(
                response: 200,
                description: 'Password reset successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Password reset successfully.'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: 'Invalid or expired OTP',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: [
                                'otp' => ['Invalid or expired OTP.']
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            )

        ]
    )]
    public function resetPassword() {}

    /**
     * Send Invitation
     */
    #[OA\Post(
        path: '/invitations/send',
        operationId: 'sendInvitation',
        tags: ['Auth'],
        summary: 'Send invitation (only for new emails, no duplicate active invitations)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'role'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        example: 'user@test.com'
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['super_admin', 'mosque_manager', 'halaqa_supervisor', 'teacher', 'parent'],
                        example: 'teacher',
                        description: 'Available roles: super_admin, mosque_manager, halaqa_supervisor, teacher, parent'
                    ),
                ]
            )
        ),
        responses: [

            // ✅ Success
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
                            example: [
                                'id' => 1,
                                'email' => 'user@test.com',
                                'role' => 'teacher',
                                'token' => 'abc123'
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),

            // 🔐 Unauthenticated
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
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ],
                    examples: [
                        new OA\Examples(
                            example: 'email_exists',
                            summary: 'User already exists',
                            value: [
                                "status" => false,
                                "message" => "Validation error.",
                                "data" => [
                                    "email" => [
                                        "User already exists and has a role."
                                    ]
                                ],
                                "pagination" => null
                            ]
                        ),
                        new OA\Examples(
                            example: 'invitation_exists',
                            summary: 'Invitation already exists',
                            value: [
                                "status" => false,
                                "message" => "Validation error.",
                                "data" => [
                                    "email" => [
                                        "An active invitation already exists for this email."
                                    ]
                                ],
                                "pagination" => null
                            ]
                        ),
                        new OA\Examples(
                            example: 'role_not_allowed',
                            summary: 'Role not allowed',
                            value: [
                                "status" => false,
                                "message" => "Validation error.",
                                "data" => [
                                    "role" => [
                                        "You are not allowed to invite this role."
                                    ]
                                ],
                                "pagination" => null
                            ]
                        ),
                    ]
                )
            ),

        ]
    )]
    public function sendInvitation() {}

    /**
     * Accept Invitation
     */
    #[OA\Post(
        path: '/invitations/accept',
        operationId: 'acceptInvitation',
        tags: ['Auth'],
        summary: 'Accept invitation and create account or attach role',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token'],
                properties: [
                    new OA\Property(
                        property: 'token',
                        type: 'string',
                        example: 'abc123token'
                    ),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Waseem',
                        description: 'Required only if user does not exist'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'password123',
                        description: 'Required only if user does not exist'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        example: 'password123',
                        description: 'Required only if user does not exist'
                    ),
                ]
            )
        ),
        responses: [

            // ✅ Success
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
                                        new OA\Property(property: 'name', type: 'string', example: 'Waseem'),
                                        new OA\Property(property: 'email', type: 'string', example: 'user@test.com'),
                                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'user_status',
                                    type: 'string',
                                    example: 'new',
                                    description: 'new or existing'
                                ),
                                new OA\Property(
                                    property: 'roles_added',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['teacher']
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),

            // ❌ Invalid / expired token
            new OA\Response(
                response: 422,
                description: 'Invalid or expired invitation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: [
                                'token' => ['Invalid or expired invitation']
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),

        ]
    )]
    public function acceptInvitation() {}
}
