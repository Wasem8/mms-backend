<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class AuthEndpoints
{
    #[OA\Post(
        path: '/auth/register-parent',
        operationId: 'registerParent',
        tags: ['Auth'],
        summary: 'Register a new parent account',
        description: 'Create a new parent account. An OTP verification email will be sent.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali', minLength: 2, maxLength: 255),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'parent@test.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123', minLength: 8),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Account created successfully. OTP sent to email.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Account created. OTP sent to your email.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 7),
                                new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                new OA\Property(property: 'email', type: 'string', example: 'parent@test.com'),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
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
    public function registerParent() {}

    #[OA\Post(
        path: '/auth/verify-otp',
        operationId: 'verifyOtp',
        tags: ['Auth'],
        summary: 'Verify OTP and activate account',
        description: 'Verify the OTP code sent to the registered email address',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'otp'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'parent@test.com'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456', maxLength: 6),


                ]
            )
        ),
        responses: [
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
    public function verifyOtp() {}

    #[OA\Post(
        path: '/auth/login',
        operationId: 'login',
        tags: ['Auth'],
        summary: 'Login with email and password',
        description: 'Authenticate and receive an access token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@test.com'),
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
                                new OA\Property(property: 'access_token', type: 'string', example: '13|xxxxxxxxxxxx'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                        new OA\Property(property: 'created_at', type: 'string', example: '2026-04-13T11:28:02.000000Z'),
                                        new OA\Property(
                                            property: 'roles',
                                            type: 'array',
                                            items: new OA\Items(type: 'string', example: 'teacher')
                                        ),
                                    ]
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Account blocked or not verified',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'EMAIL_NOT_VERIFIED'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
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
            new OA\Response(response: 429, description: 'Too many login attempts'),
        ]
    )]
    public function login() {}

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
        ]
    )]
    public function logout() {}

    #[OA\Post(
        path: '/auth/forgot-password',
        operationId: 'forgotPassword',
        tags: ['Auth'],
        summary: 'Send OTP for password reset',
        description: 'Request password reset. If email exists, an OTP will be sent (for security, always returns success).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@test.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Request processed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'If email exists, OTP has been sent.'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
        ]
    )]
    public function forgotPassword() {}

    #[OA\Post(
        path: '/auth/reset-password',
        operationId: 'resetPassword',
        tags: ['Auth'],
        summary: 'Reset password with OTP',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'otp', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@test.com'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456'),
                    new OA\Property(property: 'password', type: 'string', example: 'newpassword', minLength: 8),
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
    public function resetPassword() {}
}
