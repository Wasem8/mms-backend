<?php

namespace App\OpenApi\Shared;

use OpenApi\Attributes as OA;

/**
 * Reusable API Schema Definitions
 */
#[OA\Schema(
    schema: 'HalaqaResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'حلقة التحفيظ - المستوى الأول'),
        new OA\Property(
            property: 'schedule_days',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ["sunday", "tuesday", "thursday"]
        ),
        new OA\Property(property: 'start_time', type: 'string', example: '16:00:00'),
        new OA\Property(property: 'end_time', type: 'string', example: '18:00:00'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'capacity', type: 'integer', example: 10),
        new OA\Property(
            property: 'teacher',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 4),
                new OA\Property(property: 'name', type: 'string', example: 'Teacher')
            ]
        ),
        new OA\Property(
            property: 'mosque',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 2),
                new OA\Property(property: 'name', type: 'string', example: 'جامع الراجحي الكبير')
            ]
        ),
        new OA\Property(property: 'students_count', type: 'integer', example: 5),
    ]
)]

#[OA\Schema(
    schema: 'StudentResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 20),
        new OA\Property(property: 'first_name', type: 'string', example: 'أحمد'),
        new OA\Property(property: 'last_name', type: 'string', example: 'محمد'),
        new OA\Property(
            property: 'parent',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 5),
                new OA\Property(property: 'name', type: 'string', example: 'Parent')
            ]
        ),
        new OA\Property(
            property: 'mosque',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'جامع الراجحي الكبير')
            ]
        ),
        new OA\Property(property: 'date_of_birth', type: 'string', format: 'date', example: '2015-05-15'),
        new OA\Property(property: 'gender', type: 'string', example: 'male'),
        new OA\Property(property: 'status', type: 'string', example: 'inactive'),
    ]
)]

#[OA\Schema(
    schema: 'EvaluationResource',
    type: 'object',
    properties: [

        new OA\Property(property: 'id', type: 'integer', example: 1),

        new OA\Property(
            property: 'student',
            ref: '#/components/schemas/StudentResource'
        ),

        new OA\Property(
            property: 'halaqa',
            ref: '#/components/schemas/HalaqaResource'
        ),

        new OA\Property(property: 'score', type: 'integer', example: 95, nullable: true),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'أداء ممتاز'),
        new OA\Property(property: 'evaluated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),

    ]
)]

#[OA\Schema(
    schema: 'PaginationMeta',
    type: 'object',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer'),
        new OA\Property(property: 'last_page', type: 'integer'),
        new OA\Property(property: 'per_page', type: 'integer'),
        new OA\Property(property: 'total', type: 'integer'),
        new OA\Property(property: 'has_more_pages', type: 'boolean'),
    ]
)]



// هيكل رد الخطأ الموحد الذي طلبته
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'status', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Error message here'),
        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
    ]
)]
class Schemas
{
    /**
     * Auth Response Schema
     */
    public static function authResponseSchema()
    {
        return [
            new OA\Property(property: 'access_token', type: 'string', example: '13|xxxxxxxxxxxx'),
            new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
            new OA\Property(
                property: 'user',
                type: 'object',
                properties: self::userSchema()
            ),
        ];
    }

    /**
     * User Object Schema
     */
    public static function userSchema()
    {
        return [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
            new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
            new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'blocked'], example: 'active'),
            new OA\Property(
                property: 'roles',
                type: 'array',
                items: new OA\Items(type: 'string'),
                example: ['teacher', 'super_admin']
            ),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ];
    }

    /**
     * Auth Response Schema
     */


    /**
     * Halaqa Teacher Schema
     */
    public static function halaqaTeacherSchema()
    {
        return [
            new OA\Property(property: 'id', type: 'integer', example: 5),
            new OA\Property(property: 'name', type: 'string', example: 'Sheikh Ahmed'),
        ];
    }

    /**
     * Halaqa Resource Full Schema
     */
    public static function halaqaResourceSchema()
    {
        return [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'حلقة التجويد'),
            new OA\Property(
                property: 'schedule_days',
                type: 'array',
                items: new OA\Items(type: 'string'),
                example: ['Sunday', 'Tuesday'],
                description: 'Days of the week for classes'
            ),
            new OA\Property(property: 'start_time', type: 'string', example: '16:00', description: 'Start time in HH:mm format'),
            new OA\Property(property: 'end_time', type: 'string', example: '18:00', description: 'End time in HH:mm format'),
            new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
            new OA\Property(property: 'capacity', type: 'integer', example: 20),
            new OA\Property(
                property: 'teacher',
                type: 'object',
                properties: self::halaqaTeacherSchema()
            ),
            new OA\Property(property: 'students_count', type: 'integer', example: 15),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ];
    }

    /**
     * Invitation Schema
     */
    public static function invitationSchema()
    {
        return [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
            new OA\Property(property: 'role', type: 'string', example: 'teacher'),
            new OA\Property(property: 'token', type: 'string', example: 'abc123token'),
            new OA\Property(property: 'status', type: 'string', enum: ['pending', 'accepted', 'rejected'], example: 'pending'),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        ];
    }


}
