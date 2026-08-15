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
                required: ['email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'أحمد', minLength: 2, maxLength: 100, nullable: true),
                    new OA\Property(property: 'last_name', type: 'string', example: 'الشديد', minLength: 2, maxLength: 100, nullable: true),
                    new OA\Property(property: 'phone', type: 'string', example: '+963900000000', maxLength: 20, nullable: true),
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
                                new OA\Property(property: 'first_name', type: 'string', example: 'أحمد', nullable: true),
                                new OA\Property(property: 'last_name', type: 'string', example: 'الشديد', nullable: true),
                                new OA\Property(property: 'name', type: 'string', example: 'أحمد الشديد'),
                                new OA\Property(property: 'email', type: 'string', example: 'parent@test.com'),
                                new OA\Property(property: 'phone', type: 'string', example: '+963900000000', nullable: true),
                                new OA\Property(property: 'status', type: 'string', example: 'inactive'),
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
                            example: ['email' => ['The email has already been taken.']]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function registerParent() {}

    #[OA\Post(
        path: '/auth/register-volunteer',
        operationId: 'registerVolunteer',
        tags: ['Auth'],
        summary: 'Register a new volunteer account',
        description: 'Create a new volunteer account. An OTP verification email will be sent.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Volunteer Name', minLength: 2, maxLength: 255),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'volunteer@test.com'),
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
                        new OA\Property(property: 'message', type: 'string', example: 'Account created. An OTP has been sent to your email for verification.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 7),
                                new OA\Property(property: 'name', type: 'string', example: 'Volunteer Name'),
                                new OA\Property(property: 'email', type: 'string', example: 'volunteer@test.com'),
                                new OA\Property(property: 'status', type: 'string', example: 'inactive'),
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
    public function registerVolunteer() {}

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
                                // داخل دالة login و verifyOtp في قسم الـ Data properties:

                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                new OA\Property(property: 'email', type: 'string', example: 'admin@test.com'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'email_verified_at', type: 'string', example: '2026-05-03 14:00:00', nullable: true),
                                new OA\Property(
                                    property: 'roles',
                                    type: 'array',
                                    items: new OA\Items(type: 'string', example: 'parent')
                                ),
// إضافة هذا الجزء الهام جداً:
                                new OA\Property(
                                    property: 'permissions',
                                    type: 'array',
                                    items: new OA\Items(type: 'string', example: 'create_student')
                                ),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-05-03 12:00:00'),
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
        path: '/auth/resend-otp',
        operationId: 'resendOtp',
        tags: ['Auth'],
        summary: 'Resend OTP verification code',
        description: 'Regenerate and resend a fresh OTP verification code to the user email. Rate-limited to 1 request per minute.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'parent@test.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP code resent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'OTP verification code has been resent to your email.'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error - Email not found or invalid format',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'The selected email is invalid.'),
                        new OA\Property(property: 'data', type: 'object', example: ['email' => ['The selected email is invalid.']]),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests - Throttled for 60 seconds',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Too many attempts. Please wait before retrying.'),
                    ]
                )
            ),
        ]
    )]
    public function resendOtp() {}

    #[OA\Post(
        path: '/auth/login',
        operationId: 'login',
        tags: ['Auth'],
        summary: 'Login with email and password',
        description: "
### 🧪 Test Credentials:
| Role | Email | Password |
| :--- | :--- | :--- |
| **Super Admin** | `admin@test.com` | `password` |
| **Mosque Manager** | `manager@test.com` | `password` |
| **Supervisor** | `supervisor@test.com` | `password` |
| **Teacher** | `teacher@test.com` | `password` |
| **Parent** | `parent@test.com` | `password` |
    ",

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
                                        // داخل دالة login و verifyOtp في قسم الـ Data properties:

                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                        new OA\Property(property: 'email', type: 'string', example: 'admin@test.com'),
                                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                                        new OA\Property(property: 'email_verified_at', type: 'string', example: '2026-05-03 14:00:00', nullable: true),
                                        new OA\Property(
                                            property: 'roles',
                                            type: 'array',
                                            items: new OA\Items(type: 'string', example: 'parent')
                                        ),
// إضافة هذا الجزء الهام جداً:
                                        new OA\Property(
                                            property: 'permissions',
                                            type: 'array',
                                            items: new OA\Items(type: 'string', example: 'create_student')
                                        ),
                                        new OA\Property(property: 'created_at', type: 'string', example: '2026-05-03 12:00:00'),
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


    #[OA\Post(
        path: '/auth/refresh',
        operationId: 'refreshToken',
        tags: ['Auth'],
        summary: 'Refresh JWT token',
        description: 'Bumps the current token and returns a new one. The old token will be invalidated.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'New token generated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Token refreshed successfully.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'access_token', type: 'string'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 3600)
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function refreshToken() {}

    #[OA\Get(
        path: '/auth/me',
        operationId: 'me',
        tags: ['Auth'],
        summary: 'Get current authenticated user profile',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User profile retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                new OA\Property(property: 'email', type: 'string', example: 'admin@test.com'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-05-03 12:00:00'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me() {}

    #[OA\Post(
        path: '/auth/update-fcm-token',
        operationId: 'updateFcmToken',
        tags: ['Auth'],
        summary: 'Update user FCM notification token',
        description: 'Updates the Firebase Cloud Messaging token for the authenticated user to enable push notifications.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['fcm_token'],
                properties: [
                    new OA\Property(property: 'fcm_token', type: 'string', example: 'fcm_token_string_from_firebase_sdk'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث توكن الإشعارات بنجاح'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
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
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation Error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'The fcm token field is required.'),
                    ]
                )
            ),
        ]
    )]
    public function updateFcmToken() {}

    #[OA\Delete(
        path: '/auth/fcm-token',
        operationId: 'deleteFcmToken',
        tags: ['Auth'],
        summary: 'Delete user FCM notification token on logout',
        description: 'Flushes and removes the registered Firebase Cloud Messaging token for the authenticated user to secure privacy on shared devices.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token removed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف توكن الإشعارات بنجاح'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
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
                    ]
                )
            ),
        ]
    )]
    public function deleteFcmToken() {}

    #[OA\Patch(
        path: '/users/{user}/status',
        operationId: 'changeUserStatus',
        tags: ['Users'],
        summary: 'تغيير حالة المستخدم (تفعيل / تجميد)',
        description: 'تغيير حالة المستخدم بين active و inactive. يتطلب وجود صلاحيات مناسبة (الهرمية الوظيفية ونطاق المسجد).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'معرف المستخدم (ID)',
                schema: new OA\Schema(type: 'integer', example: 5)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        example: 'inactive',
                        description: 'الحالة الجديدة للمستخدم'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم تغيير حالة الحساب بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تجميد الحساب بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 5),
                                new OA\Property(property: 'name', type: 'string', example: 'محمد العلي'),
                                new OA\Property(property: 'email', type: 'string', example: 'user@test.com'),
                                new OA\Property(property: 'status', type: 'string', example: 'inactive'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'خطأ في التحقق أو عدم استيفاء الشروط والعدم امتلاك الصلاحيات',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'لا تملك صلاحية إدارة حالة هذا المستخدم.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: ['user' => ['لا يمكنك إدارة مستخدم خارج نطاق صلاحياتك.']]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'غير مصرح (تطلب توكن تسجيل الدخول)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function changeStatus() {}

    #[OA\Get(
        path: '/users',
        operationId: 'listUsers',
        tags: ['Users'],
        summary: 'جلب قائمة جميع المستخدمين في النظام',
        description: 'جلب قائمة المستخدمين مع دعم البحث والتصفية حسب الحالة والدور الوظيفي والترقيم (Paginated).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'البحث عن طريق الاسم، البريد الإلكتروني، أو رقم الجوال',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'تصفية حسب حالة الحساب (active أو inactive)',
                schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'])
            ),
            new OA\Parameter(
                name: 'role',
                in: 'query',
                required: false,
                description: 'تصفية حسب الدور الوظيفي (مثال: super_admin, mosque_manager, halaqa_supervisor, teacher, parent)',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'رقم الصفحة للترقيم (الصفحة الافتراضية 1)',
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب قائمة المستخدمين بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب قائمة المستخدمين بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'first_name', type: 'string', example: 'أحمد', nullable: true),
                                    new OA\Property(property: 'last_name', type: 'string', example: 'علي', nullable: true),
                                    new OA\Property(property: 'name', type: 'string', example: 'أحمد علي'),
                                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                    new OA\Property(property: 'phone', type: 'string', example: '+963900000000', nullable: true),
                                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    new OA\Property(property: 'email_verified_at', type: 'string', example: '2026-05-03 14:00:00', nullable: true),
                                    new OA\Property(
                                        property: 'roles',
                                        type: 'array',
                                        items: new OA\Items(type: 'string', example: 'teacher')
                                    ),
                                    new OA\Property(
                                        property: 'permissions',
                                        type: 'array',
                                        items: new OA\Items(type: 'string', example: 'view_students')
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2026-05-03 12:00:00'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 68),
                                new OA\Property(property: 'has_more_pages', type: 'boolean', example: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'غير مصرح للوصول',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'غير مصرح لك بالوصول'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function index() {}

}
