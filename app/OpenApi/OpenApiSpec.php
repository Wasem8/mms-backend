<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * MMS API Documentation - OpenAPI/Swagger Configuration
 */
#[OA\Info(
    title: 'MMS API Documentation',
    version: "1.0.0",
    description: 'API documentation for Mosque Management System (MMS)',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'support@example.com',
        url: 'https://mms-support.example.com'
    ),
    license: new OA\License(
        name: 'MIT',
        identifier: 'MIT'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Local Development Server'
)]
#[OA\Server(
    url: 'https://api.mms-system.com',
    description: 'Production API Server'
)]

// ========== SECURITY SCHEMES ==========
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Enter your Bearer token'
)]

// ========== GLOBAL COMPONENTS (Responses & Schemas) ==========
#[OA\Components(
    responses: [
        // استجابة 401: غير مسجل دخول
        'Unauthenticated' => new OA\Response(
            response: 'Unauthenticated',
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

        // استجابة 403: صلاحيات غير كافية (التي طلبتها)
        'Forbidden' => new OA\Response(
            response: 'Forbidden',
            description: 'Forbidden - Insufficient permissions',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Access denied. Your role does not allow this action.'),
                    new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                    new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                ]
            )
        ),

        // استجابة 404: مورد غير موجود
        'NotFound' => new OA\Response(
            response: 'NotFound',
            description: 'Resource not found',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
                    new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                    new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                ]
            )
        ),

        // استجابة 422: خطأ في التحقق من البيانات
        'ValidationError' => new OA\Response(
            response: 'ValidationError',
            description: 'Validation failed',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                    new OA\Property(property: 'data', type: 'object', example: ["field" => ["Error details"]]),
                    new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                ]
            )
        )
    ],
    schemas: [
        // تعريف الـ Pagination لكي يعمل ref: '#/components/schemas/Pagination'
        'Pagination' => new OA\Schema(
            schema: 'Pagination',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                new OA\Property(property: 'total', type: 'integer', example: 50),
                new OA\Property(property: 'has_more_pages', type: 'boolean', example: true),
            ]
        )
    ]
)]

class OpenApiSpec
{
}
