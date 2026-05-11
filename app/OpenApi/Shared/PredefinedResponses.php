<?php

namespace App\OpenApi\Shared;

use OpenApi\Attributes as OA;

/**
 * Predefined Response Objects for reuse in attributes
 * These must be defined as constants or direct object instantiation
 */
class PredefinedResponses
{
    public static array $validationError;
    public static array $unauthorized;
    public static array $forbidden;
    public static array $notFound;

    public static function init(): void
    {
        self::$validationError = [
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
        ];

        self::$unauthorized = [
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
        ];

        self::$forbidden = [
            new OA\Response(
                response: 403,
                description: 'Forbidden - Insufficient permissions for this action',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ];

        self::$notFound = [
            new OA\Response(
                response: 404,
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
        ];
    }
}

// Initialize on load
PredefinedResponses::init();
