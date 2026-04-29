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
/*
class MosqueSpec
{
    // ==========================================
    // PUBLIC MOSQUE ENDPOINTS
    // ==========================================

    #[OA\Get(
        path: '/mosques',
        operationId: 'getMosques',
        tags: ['Mosques'],
        summary: 'Get a list of all mosques (Public)',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation')
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/mosques/featured',
        operationId: 'getFeaturedMosques',
        tags: ['Mosques'],
        summary: 'Get featured mosques (Public)',
        responses: [
            new OA\Response(response: 200, description: 'Featured mosques retrieved successfully')
        ]
    )]
    public function featured() {}

    #[OA\Get(
        path: '/mosques/city/{city}',
        operationId: 'getMosquesByCity',
        tags: ['Mosques'],
        summary: 'Get mosques filtered by city (Public)',
        parameters: [
            new OA\Parameter(name: 'city', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mosques retrieved successfully')
        ]
    )]
    public function byCity() {}

    #[OA\Get(
        path: '/mosques/{id}',
        operationId: 'showMosque',
        tags: ['Mosques'],
        summary: 'Get details of a specific mosque (Public)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mosque details retrieved successfully'),
            new OA\Response(response: 404, description: 'Mosque not found')
        ]
    )]
    public function show() {}

    // ==========================================
    // PROTECTED MOSQUE ENDPOINTS (Auth Required)
    // ==========================================


    #[OA\Post(
        path: '/mosques',
        operationId: 'storeMosque',
        tags: ['Mosques'],
        summary: 'Create a new mosque',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'city', 'district', 'status'], // يمكنك تعديل الحقول الإجبارية حسب الـ Request الخاص بك
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'مسجد النور'),
                    new OA\Property(property: 'cover_image', type: 'string', nullable: true, example: 'https://example.com/mosque.jpg'),
                    new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '04:00 AM - 10:00 PM'),
                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                    new OA\Property(property: 'is_featured', type: 'boolean', example: false),
                    new OA\Property(property: 'city', type: 'string', example: 'Riyadh'),
                    new OA\Property(property: 'district', type: 'string', example: 'Al-Malqa'),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 24.7135517),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 46.6752957),
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5),
                    new OA\Property(property: 'reviews_count', type: 'integer', example: 120),
                    new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'الشيخ عبدالله'),
                    new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'الشيخ محمد'),
                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Mosque created successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function store() {}


    #[OA\Put(
        path: '/mosques/{mosque}',
        operationId: 'updateMosque',
        tags: ['Mosques'],
        summary: 'Update an existing mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'مسجد النور (محدث)'),
                    new OA\Property(property: 'cover_image', type: 'string', nullable: true),
                    new OA\Property(property: 'working_hours', type: 'string', nullable: true),
                    new OA\Property(property: 'status', type: 'string', example: 'maintenance'),
                    new OA\Property(property: 'is_featured', type: 'boolean'),
                    new OA\Property(property: 'city', type: 'string'),
                    new OA\Property(property: 'district', type: 'string'),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float'),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float'),
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float'),
                    new OA\Property(property: 'reviews_count', type: 'integer'),
                    new OA\Property(property: 'imam', type: 'string', nullable: true),
                    new OA\Property(property: 'khatib', type: 'string', nullable: true),
                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mosque updated successfully')
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/mosques/{mosque}',
        operationId: 'destroyMosque',
        tags: ['Mosques'],
        summary: 'Delete a mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mosque deleted successfully')
        ]
    )]
    public function destroy() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/status',
        operationId: 'updateMosqueStatus',
        tags: ['Mosques'],
        summary: 'Update mosque status',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'maintenance')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Status updated successfully')
        ]
    )]
    public function updateStatus() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/featured',
        operationId: 'toggleMosqueFeatured',
        tags: ['Mosques'],
        summary: 'Toggle if the mosque is featured',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Featured status toggled successfully')
        ]
    )]
    public function toggleFeatured() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/rating',
        operationId: 'updateMosqueRating',
        tags: ['Mosques'],
        summary: 'Update the average rating and review count of the mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.8),
                    new OA\Property(property: 'reviews_count', type: 'integer', example: 121)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Rating updated successfully')
        ]
    )]
    public function updateRating() {}


    // ==========================================
    // FACILITIES ENDPOINTS
    // ==========================================

    #[OA\Get(
        path: '/{mosque}/facilities',
        operationId: 'getPublicFacilities',
        tags: ['Facilities'],
        summary: 'Get all facilities for a mosque (Public)',
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Facilities retrieved successfully')
        ]
    )]
    public function indexFacilities() {}

    #[OA\Get(
        path: '/mosques/{mosque}/facilities',
        operationId: 'getFacilitiesByMosque',
        tags: ['Facilities'],
        summary: 'Get attached facilities for a mosque (Auth required)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Attached facilities retrieved successfully')
        ]
    )]
    public function byMosqueFacilities() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/sync',
        operationId: 'syncFacilities',
        tags: ['Facilities'],
        summary: 'Sync facilities for a mosque (replaces old ones)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'facility_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Facilities synced successfully')
        ]
    )]
    public function syncFacilities() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/attach',
        operationId: 'attachFacility',
        tags: ['Facilities'],
        summary: 'Attach a specific facility to a mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'facility_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Facility attached successfully')
        ]
    )]
    public function attachFacility() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/detach',
        operationId: 'detachFacility',
        tags: ['Facilities'],
        summary: 'Detach a specific facility from a mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'facility_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Facility detached successfully')
        ]
    )]
    public function detachFacility() {}

}
*/
