<?php

namespace App\OpenApi\Shared;

use OpenApi\Attributes as OA;

/**
 * Predefined API Response Objects
 * Used in endpoint attributes (constant expressions only)
 */
class ApiResponses
{
    /**
     * Standard validation error response
     */
    public static function validationError(): OA\Response
    {
        return new OA\Response(
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
        );
    }

    /**
     * Standard authentication error response
     */
    public static function unauthorized(): OA\Response
    {
        return new OA\Response(
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
        );
    }

    /**
     * Standard forbidden/insufficient permissions response
     */
    public static function forbidden(): OA\Response
    {
        return new OA\Response(
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
        );
    }

    /**
     * Standard not found response
     */
    public static function notFound(): OA\Response
    {
        return new OA\Response(
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
        );
    }

    /**
     * Business logic error (e.g., capacity exceeded)
     */
    public static function businessError(string $message = 'Business logic error'): OA\Response
    {
        return new OA\Response(
            response: 400,
            description: $message,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: $message),
                    new OA\Property(property: 'data', type: 'object', example: []),
                    new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                ]
            )
        );
    }

    /**
     * Too many requests response
     */
    public static function tooManyRequests(): OA\Response
    {
        return new OA\Response(
            response: 429,
            description: 'Too many requests - Rate limit exceeded'
        );
    }
}
