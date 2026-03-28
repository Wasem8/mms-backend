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
#[OA\Tag(name: 'Auth', description: 'Authentication endpoints including OTP login and Join Requests')]

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your Bearer token to access protected endpoints'
)]
class OpenApiSpec
{
    /**
     * Join Request (Public)
     */
    #[OA\Post(
        path: '/auth/join-request',
        operationId: 'submitJoinRequest',
        tags: ['Auth'],
        summary: 'Submit a new student registration request',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation', 'age', 'grade', 'parent_phone'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ahmed Mohamed'),
                    new OA\Property(property: 'email', type: 'string', example: 'ahmed@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
                    new OA\Property(property: 'age', type: 'integer', example: 12),
                    new OA\Property(property: 'grade', type: 'string', example: '6th Grade'),
                    new OA\Property(property: 'parent_phone', type: 'string', example: '0501234567'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Request submitted successfully'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function submitJoinRequest() {}


    /**
     * Login (Request OTP)
     */
    #[OA\Post(
        path: '/auth/login',
        operationId: 'login',
        tags: ['Auth'],
        summary: 'Login and send OTP to email',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'wasem8115@gmail.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OTP sent to email'),
            new OA\Response(response: 422, description: 'Validation Error (Returns JSON because of Accept header)')
        ]
    )]
    public function login() {}

    /**
     * Verify OTP
     */
    #[OA\Post(
        path: '/auth/verify-otp',
        operationId: 'verifyOtp',
        tags: ['Auth'],
        summary: 'Verify OTP and get Access Token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'otp'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'wasem8115@gmail.com'),
                    new OA\Property(property: 'otp', type: 'string', example: '726437'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful'),
            new OA\Response(response: 422, description: 'Invalid or expired OTP')
        ]
    )]
    public function verifyOtp() {}

    /**
     * Logout (Authenticated)
     */
    #[OA\Post(
        path: '/auth/logout',
        operationId: 'logout',
        tags: ['Auth'],
        summary: 'Logout current user',
        security: [['bearerAuth' => []]], // ربط تلقائي بالتوكن المحفوظ
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function logout() {}
}
