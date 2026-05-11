<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * MMS API Documentation - OpenAPI/Swagger Configuration
 */

#[OA\OpenAPI(
    openapi: '3.0.0'
)]

#[OA\Info(
    title: 'MMS API Documentation',
    version: '1.0.0',
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
    url: 'http://localhost:8000',
    description: 'Local Development Server'
)]

#[OA\Server(
    url: 'https://mms-backend-rose.vercel.app/api',
    description: 'Production API Server'
)]

/*
|--------------------------------------------------------------------------
| Security Scheme
|--------------------------------------------------------------------------
*/

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter JWT Bearer token'
)]

/*
|--------------------------------------------------------------------------
| Global Parameters
|--------------------------------------------------------------------------
*/

#[OA\Parameter(
    parameter: 'AcceptLanguageHeader',
    name: 'Accept-Language',
    in: 'header',
    required: false,
    description: 'حدد اللغة: ar أو en',
    schema: new OA\Schema(
        type: 'string',
        enum: ['ar', 'en'],
        default: 'ar'
    )
)]

/*
|--------------------------------------------------------------------------
| Global Components
|--------------------------------------------------------------------------
*/

#[OA\Components(

    /*
    |--------------------------------------------------------------------------
    | Responses
    |--------------------------------------------------------------------------
    */

    responses: [

        new OA\Response(
            response: 'Unauthenticated',
            description: 'غير مسجل دخول - توكن مفقود أو غير صحيح',
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
                        example: 'Unauthenticated.'
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
            response: 'Forbidden',
            description: 'صلاحيات غير كافية',
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
                        example: 'Access denied. Your role does not allow this action.'
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
            response: 'NotFound',
            description: 'المورد غير موجود',
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
                        example: 'Resource not found.'
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
            response: 'ValidationError',
            description: 'خطأ في التحقق من البيانات',
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
                        example: 'Validation error.'
                    ),

                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        example: [
                            'field' => ['Error details']
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Schemas
    |--------------------------------------------------------------------------
    */

    schemas: [

        new OA\Schema(
            schema: 'Pagination',
            type: 'object',

            properties: [

                new OA\Property(
                    property: 'current_page',
                    type: 'integer',
                    example: 1
                ),

                new OA\Property(
                    property: 'last_page',
                    type: 'integer',
                    example: 5
                ),

                new OA\Property(
                    property: 'per_page',
                    type: 'integer',
                    example: 10
                ),

                new OA\Property(
                    property: 'total',
                    type: 'integer',
                    example: 50
                ),

                new OA\Property(
                    property: 'has_more_pages',
                    type: 'boolean',
                    example: true
                ),
            ]
        ),
    ]
)]

class OpenApiSpec
{
}
