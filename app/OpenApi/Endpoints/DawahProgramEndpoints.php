<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;


class DawahProgramEndpoints
{
    // ─────────────────────────────────────────────
    //  GET PROGRAMS BY MOSQUE
    // ─────────────────────────────────────────────
    #[OA\Get(
        path: '/Program/mosques/{mosque}/dawah_programs',
        operationId: 'getMosqueDawahPrograms',
        tags: ['Dawah Programs'],
        summary: 'List Dawah programs',
        description: 'Retrieve all Dawah programs belonging to a specific mosque.',
        parameters: [
            new OA\Parameter(
                name: 'mosque',
                in: 'path',
                required: true,
                description: 'Mosque ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Programs retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب البرامج بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'space_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'program_name', type: 'string', example: 'درس القرآن'),
                                    new OA\Property(property: 'description', type: 'string', example: 'درس لتعليم القرآن الكريم'),
                                    new OA\Property(property: 'image', type: 'string', example: 'path/to/image.jpg'),
                                    new OA\Property(property: 'presenter', type: 'string', example: 'الشيخ أحمد'),
                                    new OA\Property(property: 'start_time', type: 'string', format: 'time', example: '10:00'),
                                    new OA\Property(property: 'end_time', type: 'string', format: 'time', example: '11:00'),
                                    new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-05-10'),
                                    new OA\Property(property: 'level', type: 'string', example: 'beginner'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    public function index() {}

    // Additional methods for show, store, update, and destroy...



// ─────────────────────────────────────────────
//  SHOW PROGRAM
// ─────────────────────────────────────────────
#[OA\Get(
        path: '/Program/mosques/{mosque}/dawah_programs/{program}',
        operationId: 'getSingleDawahProgram',
        tags: ['Dawah Programs'],
        summary: 'Get single Dawah program',
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Program retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب تفاصيل البرنامج بنجاح'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'mosque_id', type: 'integer'),
                                new OA\Property(property: 'space_id', type: 'integer'),
                                new OA\Property(property: 'program_name', type: 'string'),
                                new OA\Property(property: 'description', type: 'string'),
                                new OA\Property(property: 'image', type: 'string'),
                                new OA\Property(property: 'presenter', type: 'string'),
                                new OA\Property(property: 'start_time', type: 'string', format: 'time'),
                                new OA\Property(property: 'end_time', type: 'string', format: 'time'),
                                new OA\Property(property: 'date', type: 'string', format: 'date'),
                                new OA\Property(property: 'level', type: 'string'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found')
        ]
    )]
    public function show() {}

    // ─────────────────────────────────────────────
    //  CREATE PROGRAM
    // ─────────────────────────────────────────────
    #[OA\Post(
        path: '/Program/mosques/{mosque}/dawah_programs',
                operationId: 'createDawahProgram',
        tags: ['Dawah Programs'],
        summary: 'Create new Dawah program',
        security: [['bearerAuth' => []]],
        parameters: [
        new OA\Parameter(name: 'mosque', in: 'path', required: true, example:4, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: [ 'space_id', 'program_name', 'start_time', 'end_time', 'date'],
                    properties: [
                        new OA\Property(
                            property: 'space_id',
                            type: 'integer',
                            example: 1
                        ),

                        new OA\Property(
                            property: 'program_name',
                            type: 'string',
                            example: 'درس القرآن'
                        ),

                        new OA\Property(
                            property: 'description',
                            type: 'string',
                            example: 'درس لتعليم القرآن الكريم'
                        ),

                        new OA\Property(
                            property: 'image',
                            type: 'string',
                            format: 'binary'
                        ),

                        new OA\Property(
                            property: 'presenter',
                            type: 'string',
                            example: 'الشيخ أحمد'
                        ),

                        new OA\Property(
                            property: 'start_time',
                            type: 'string',
                            example: '10:00'
                        ),

                        new OA\Property(
                            property: 'end_time',
                            type: 'string',
                            example: '11:00'
                        ),

                        new OA\Property(
                            property: 'date',
                            type: 'string',
                            format: 'date',
                            example: '2026-05-10'
                        ),

                        new OA\Property(
                            property: 'level',
                            type: 'string',
                            enum: ['beginner', 'intermediate', 'advanced'],
                            example: 'beginner'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Program created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تمت إضافة البرنامج بنجاح'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    public function store() {}

    // ─────────────────────────────────────────────
    //  UPDATE PROGRAM
    // ─────────────────────────────────────────────
    #[OA\Put(
        path: '/Program/mosques/{mosque}/dawah_programs/{program}',
        operationId: 'updateDawahProgram',
        tags: ['Dawah Programs'],
        summary: 'Update Dawah program',
        security: [['bearerAuth' => []]],

        parameters: [

            new OA\Parameter(
                name: 'mosque',
                description: 'Mosque ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),

            new OA\Parameter(
                name: 'program',
                description: 'Program ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 5
                )
            ),
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',

                schema: new OA\Schema(
                    properties: [

                        new OA\Property(
                            property: 'space_id',
                            type: 'integer',
                            example: 1
                        ),

                        new OA\Property(
                            property: 'program_name',
                            type: 'string',
                            example: 'درس القرآن'
                        ),

                        new OA\Property(
                            property: 'description',
                            type: 'string',
                            example: 'درس لتعليم القرآن الكريم'
                        ),

                        new OA\Property(
                            property: 'image',
                            type: 'string',
                            format: 'binary'
                        ),

                        new OA\Property(
                            property: 'presenter',
                            type: 'string',
                            example: 'الشيخ أحمد'
                        ),

                        new OA\Property(
                            property: 'start_time',
                            type: 'string',
                            example: '10:00'
                        ),

                        new OA\Property(
                            property: 'end_time',
                            type: 'string',
                            example: '11:00'
                        ),

                        new OA\Property(
                            property: 'date',
                            type: 'string',
                            format: 'date',
                            example: '2026-05-10'
                        ),

                        new OA\Property(
                            property: 'level',
                            type: 'string',
                            enum: ['beginner', 'intermediate', 'advanced'],
                            example: 'beginner'
                        ),
                    ]
                )
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'Program updated successfully',

                content: new OA\JsonContent(
                    properties: [

                        new OA\Property(
                            property: 'status',
                            type: 'boolean',
                            example: true
                        ),

                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم تعديل البرنامج بنجاح'
                        ),

                        new OA\Property(
                            property: 'data',
                            type: 'object'
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthorized',

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
                            example: 'Unauthorized'
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: 'Program or Mosque not found',

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
                            example: 'Resource not found'
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 409,
                description: 'Schedule conflict',

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
                            example: 'Conflict detected'
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error',

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
                            example: 'Validation error'
                        ),

                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'program_name' => [
                                    'The program name field is required.'
                                ],
                                'level' => [
                                    'The selected level is invalid.'
                                ]
                            ]
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 500,
                description: 'Server error',

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
                            example: 'Internal server error'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function update() {}

    // ─────────────────────────────────────────────
    //  DELETE PROGRAM
    // ─────────────────────────────────────────────
    #[OA\Delete(
        path: '/Program/mosques/{mosque}/dawah_programs/{program}',
        operationId: 'deleteDawahProgram',
        tags: ['Dawah Programs'],
        summary: 'Delete Dawah program',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Program deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف البرنامج بنجاح')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found')
        ]
    )]
    public function destroy() {}
}
