<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class DawahProgramEndpoints
{

    #[OA\Get(
        path: '/program/dawah_programs',
        operationId: 'getAllDawahPrograms',
        tags: ['Dawah Programs'],
        summary: 'List all Dawah programs across all mosques',
        description: 'Returns a paginated list of all Dawah programs across all mosques with their schedules. No authentication required.',
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Number of programs per page (default: 10)',
                schema: new OA\Schema(type: 'integer', example: 10)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [

            new OA\Response(
                response: 200,
                description: 'Programs retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب جميع البرامج بنجاح'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/DawahProgramWithSchedules')
                                ),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                                new OA\Property(property: 'total', type: 'integer', example: 48),
                                new OA\Property(property: 'next_page_url', type: 'string', nullable: true, example: 'https://api.example.com/Program/dawah_programs?page=2'),
                                new OA\Property(property: 'prev_page_url', type: 'string', nullable: true, example: null),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),
        ]
    )]
    public function index() {}
    // ─────────────────────────────────────────────
    //  LIST PROGRAMS BY MOSQUE
    // ─────────────────────────────────────────────
    #[OA\Get(
        path: '/program/mosques/{mosque}/dawah_programs',
        operationId: 'getMosqueDawahPrograms',
        tags: ['Dawah Programs'],
        summary: 'List all Dawah programs for a mosque',
        description: 'Returns all Dawah programs belonging to a specific mosque along with their schedules. No authentication required.',
        parameters: [
            new OA\Parameter(
                name: 'mosque',
                in: 'path',
                required: true,
                description: 'The ID of the mosque',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [

            new OA\Response(
                response: 200,
                description: 'Programs retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب برامج المسجد بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/DawahProgramWithSchedules')
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: 'Mosque not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'المسجد غير موجود'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),
        ]
    )]
    public function getProgramsByMosque() {}

    // ─────────────────────────────────────────────
    //  SHOW SINGLE PROGRAM
    // ─────────────────────────────────────────────
    #[OA\Get(
        path: '/program/mosques/{mosque}/dawah_programs/{program}',
        operationId: 'getSingleDawahProgram',
        tags: ['Dawah Programs'],
        summary: 'Get a single Dawah program',
        description: 'Returns a single Dawah program with its full details and all associated schedules.',
        parameters: [
            new OA\Parameter(
                name: 'mosque',
                in: 'path',
                required: true,
                description: 'The ID of the mosque',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'program',
                in: 'path',
                required: true,
                description: 'The ID of the Dawah program',
                schema: new OA\Schema(type: 'integer', example: 3)
            ),
        ],
        responses: [

            new OA\Response(
                response: 200,
                description: 'Program retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب البرنامج بنجاح'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/DawahProgramWithSchedules'),
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: 'Program or mosque not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'البرنامج غير موجود'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),
        ]
    )]
    public function show() {}

    // ─────────────────────────────────────────────
    //  CREATE PROGRAM
    // ─────────────────────────────────────────────
    #[OA\Post(
        path: '/program/mosques/{mosque}/dawah_programs',
        operationId: 'createDawahProgram',
        tags: ['Dawah Programs'],
        summary: 'Create Dawah program (multipart)',
        security: [['bearerAuth' => []]],

        parameters: [
            new OA\Parameter(
                name: 'mosque',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['space_id', 'program_name', 'type', 'presenter', 'schedules'],

                    properties: [

                        new OA\Property(property: 'space_id', type: 'integer', example: 2),

                        new OA\Property(property: 'program_name', type: 'string', example: 'درس القرآن'),

                        new OA\Property(property: 'description', type: 'string', nullable: true),

                        new OA\Property(
                            property: 'type',
                            type: 'string',
                            enum: ['lecture', 'course', 'competition', 'other']
                        ),

                        // ✅ IMAGE FILES (IMPORTANT)
                        new OA\Property(
                            property: 'image',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),

                        new OA\Property(
                            property: 'presenter',
                            type: 'string',
                            example: 'الشيخ أحمد'
                        ),

                        new OA\Property(
                            property: 'presenter_image',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),

                        new OA\Property(
                            property: 'is_featured',
                            type: 'boolean',
                            example: false
                        ),

                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            enum: ['active', 'inactive'],
                            example: 'active'
                        ),

                        new OA\Property(
                            property: 'level',
                            type: 'string',
                            enum: ['beginner', 'intermediate', 'advanced']
                        ),

                        new OA\Property(
                            property: 'schedules',
                            type: 'array',
                            items: new OA\Items(
                                required: ['date', 'start_time', 'end_time'],
                                properties: [
                                    new OA\Property(property: 'title', type: 'string', nullable: true, example: 'الجلسة الاولى'),
                                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'هذه ملاحظات خاصة بالجلسة'),
                                    new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-07-01'),
                                    new OA\Property(property: 'start_time', type: 'string', example: '09:00'),
                                    new OA\Property(property: 'end_time', type: 'string', example: '10:30'),
                                ]
                            )
                        ),
                    ]
                )
            )
        ),

        responses: [
            new OA\Response(
                response: 201,
                description: 'Created successfully'
            )
        ]
    )]
    public function store() {}

    // ─────────────────────────────────────────────
    //  UPDATE PROGRAM
    // ─────────────────────────────────────────────
    #[OA\Post(
        path: '/program/mosques/{mosque}/dawah_programs/{program}',
        operationId: 'updateDawahProgram',
        tags: ['Dawah Programs'],
        summary: 'Update a Dawah program',
        description: 'Updates an existing Dawah program. Send as `multipart/form-data` with `_method=PUT`. If `schedules` is provided, it **replaces** all existing schedules. Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'mosque',
                in: 'path',
                required: true,
                description: 'The ID of the mosque',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'program',
                in: 'path',
                required: true,
                description: 'The ID of the Dawah program to update',
                schema: new OA\Schema(type: 'integer', example: 3)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [

                        new OA\Property(
                            property: '_method',
                            type: 'string',
                            description: 'Laravel method spoofing — must be PUT',
                            example: 'PUT'
                        ),

                        new OA\Property(property: 'space_id', type: 'integer', example: 2),
                        new OA\Property(property: 'program_name', type: 'string', example: 'درس السيرة النبوية'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'دراسة تفصيلية للسيرة النبوية الشريفة'),
                        new OA\Property(
                            property: 'type',
                            type: 'string',
                            enum: ['lecture', 'course', 'competition', 'other'],
                            example: 'lecture'
                        ),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'presenter', type: 'string', example: 'الشيخ محمد العريفي'),
                        new OA\Property(property: 'presenter_image', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'is_featured', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            enum: ['active', 'inactive'],
                            example: 'active'
                        ),
                        new OA\Property(
                            property: 'level',
                            type: 'string',
                            enum: ['beginner', 'intermediate', 'advanced'],
                            example: 'intermediate'
                        ),

                        new OA\Property(
                            property: 'schedules',
                            type: 'array',
                            description: 'Optional — replaces ALL existing schedules if provided',
                            items: new OA\Items(
                                required: ['date', 'start_time', 'end_time'],
                                properties: [
                                    new OA\Property(property: 'title', type: 'string', nullable: true, example: 'الجلسة المحدّثة'),
                                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'تم تغيير موعد الجلسة'),
                                    new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-07-01'),
                                    new OA\Property(property: 'start_time', type: 'string', example: '09:00'),
                                    new OA\Property(property: 'end_time', type: 'string', example: '10:30'),
                                ]
                            )
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
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث البرنامج بنجاح'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/DawahProgramWithSchedules'),
                    ]
                )
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated — missing or expired token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'غير مصرح. يرجى تسجيل الدخول أولاً'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 403,
                description: 'Forbidden — authenticated but not the mosque manager',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'ليس لديك صلاحية لتعديل هذا البرنامج'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: 'Program or mosque not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'البرنامج غير موجود في هذا المسجد'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 409,
                description: 'Schedule conflict — the space is already booked at that time',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Conflict detected on 2026-07-01 between 09:00 and 10:30'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'خطأ في التحقق من البيانات'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'type'                   => ['نوع البرنامج غير صالح'],
                                'level'                  => ['مستوى البرنامج غير صالح'],
                                'schedules.0.end_time'   => ['وقت النهاية يجب أن يكون بعد وقت البداية'],
                                'schedules.0.date'       => ['صيغة التاريخ يجب أن تكون YYYY-MM-DD'],
                            ]
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
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
        path: '/program/mosques/{mosque}/dawah_programs/{program}',
        operationId: 'deleteDawahProgram',
        tags: ['Dawah Programs'],
        summary: 'Delete a Dawah program',
        description: 'Permanently deletes a Dawah program along with all its schedules and uploaded images. Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'mosque',
                in: 'path',
                required: true,
                description: 'The ID of the mosque',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'program',
                in: 'path',
                required: true,
                description: 'The ID of the program to delete',
                schema: new OA\Schema(type: 'integer', example: 3)
            ),
        ],
        responses: [

            new OA\Response(
                response: 200,
                description: 'Program deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف البرنامج بنجاح'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated — missing or expired token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'غير مصرح. يرجى تسجيل الدخول أولاً'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 403,
                description: 'Forbidden — not the mosque manager',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'ليس لديك صلاحية لحذف هذا البرنامج'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: 'Program does not belong to this mosque',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'البرنامج غير موجود في هذا المسجد'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),

            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),
        ]
    )]
    public function destroy() {}


    #[OA\Get(
        path: '/program/dawah_programs/{program}/schedules',
        operationId: 'getProgramSchedules',
        tags: ['Program Schedules'],
        summary: 'List all sessions for a Dawah program',
        description: <<<'MD'
            Returns a paginated list of schedule entries (sessions) for one Dawah program, ordered by date and start time.

            **Auth:** Not required.
            **Filters:** `date`, `from_date`, `to_date` (query string).
            MD,
        parameters: [
            new OA\Parameter(name: 'program',   in: 'path',  required: true,  description: 'Program ID',            schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page',  in: 'query', required: false, description: 'Items per page',        schema: new OA\Schema(type: 'integer', example: 10)),
            new OA\Parameter(name: 'date',      in: 'query', required: false, description: 'Exact date (Y-m-d), Can be empty',    schema: new OA\Schema(type: 'string', format: 'date', example: '2026-07-01')),
            new OA\Parameter(name: 'from_date', in: 'query', required: false, description: 'Start of date range ,Can be empty',  schema: new OA\Schema(type: 'string', format: 'date', example: '2026-07-01')),
            new OA\Parameter(name: 'to_date',   in: 'query', required: false, description: 'End of date range ,Can be empty',    schema: new OA\Schema(type: 'string', format: 'date', example: '2026-07-31')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schedules retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'تم جلب جدول البرنامج بنجاح'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProgramSchedule')),
                            new OA\Property(property: 'current_page',  type: 'integer', example: 1),
                            new OA\Property(property: 'last_page',     type: 'integer', example: 2),
                            new OA\Property(property: 'per_page',      type: 'integer', example: 10),
                            new OA\Property(property: 'total',         type: 'integer', example: 12),
                            new OA\Property(property: 'next_page_url', type: 'string',  nullable: true),
                            new OA\Property(property: 'prev_page_url', type: 'string',  nullable: true),
                        ]
                    ),
                ])
            ),
            new OA\Response(
                response: 404,
                description: 'Program not found',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string',  example: 'البرنامج غير موجود'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),
        ]
    )]
    public function scheduleIndex() {}

    #[OA\Get(
        path: '/program/dawah_programs/{program}/schedules/{schedule}',
        operationId: 'getSingleProgramSchedule',
        tags: ['Program Schedules'],
        summary: 'Get a single schedule entry',
        description: 'Returns the full details of one schedule (session) entry. **Auth:** Not required.',
        parameters: [
            new OA\Parameter(name: 'program',  in: 'path', required: true, description: 'Program ID',  schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'schedule', in: 'path', required: true, description: 'Schedule ID', schema: new OA\Schema(type: 'integer', example: 7)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schedule retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'تم جلب الجلسة بنجاح'),
                    new OA\Property(property: 'data',    ref: '#/components/schemas/ProgramSchedule'),
                ])
            ),
            new OA\Response(
                response: 404,
                description: 'Schedule not found',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string',  example: 'الجلسة غير موجودة'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),        ]
    )]
    public function scheduleShow() {}


    #[OA\Post(
        path: '/program/mosques/{mosque}/dawah_programs/{program}/schedules',
        operationId: 'createProgramSchedule',
        tags: ['Program Schedules'],
        summary: 'Add a session to a Dawah program',
        description: 'Creates a single schedule entry for the given program. **Auth:** `Bearer token` · **Role:** `mosque_manager`',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque',  in: 'path', required: true, description: 'Mosque ID',  schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'program', in: 'path', required: true, description: 'Program ID', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['date', 'start_time', 'end_time'],
                properties: [
                    new OA\Property(property: 'title',      type: 'string', nullable: true, example: 'الجلسة الأولى'),
                    new OA\Property(property: 'notes',      type: 'string', nullable: true, example: 'إحضار المصحف'),
                    new OA\Property(property: 'date',       type: 'string', format: 'date', example: '2026-07-05'),
                    new OA\Property(property: 'start_time', type: 'string', example: '09:00'),
                    new OA\Property(property: 'end_time',   type: 'string', example: '10:30'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Schedule created successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'تم إنشاء الجلسة بنجاح'),
                    new OA\Property(property: 'data',    ref: '#/components/schemas/ProgramSchedule'),
                ])
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string',  example: 'خطأ في التحقق من البيانات'),
                    new OA\Property(property: 'errors',  type: 'object',  example: [
                        'date'       => ['حقل التاريخ مطلوب'],
                        'end_time'   => ['وقت النهاية يجب أن يكون بعد وقت البداية'],
                    ]),
                ])
            ),
            new OA\Response(
                response: 404,
                description: 'Program not found',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string',  example: 'البرنامج غير موجود'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),        ]
    )]
    public function scheduleStore() {}

    #[OA\Put(
        path: '/program/mosques/{mosque}/dawah_programs/{program}/schedules/{schedule}',
        operationId: 'updateProgramSchedule',
        tags: ['Program Schedules'],
        summary: 'Update a schedule entry',
        description: 'Partially or fully updates one schedule entry. All fields are optional (`sometimes`). **Auth:** `Bearer token` · **Role:** `mosque_manager`',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque',   in: 'path', required: true, description: 'Mosque ID',   schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'program',  in: 'path', required: true, description: 'Program ID',  schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'schedule', in: 'path', required: true, description: 'Schedule ID', schema: new OA\Schema(type: 'integer', example: 7)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'title',      type: 'string', nullable: true, example: 'الجلسة المحدّثة'),
                new OA\Property(property: 'notes',      type: 'string', nullable: true, example: 'تم تغيير موعد الجلسة'),
                new OA\Property(property: 'date',       type: 'string', format: 'date', example: '2026-07-10'),
                new OA\Property(property: 'start_time', type: 'string', example: '10:00'),
                new OA\Property(property: 'end_time',   type: 'string', example: '11:30'),
            ])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schedule updated successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'تم تحديث الجلسة بنجاح'),
                    new OA\Property(property: 'data',    ref: '#/components/schemas/ProgramSchedule'),
                ])
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string',  example: 'خطأ في التحقق من البيانات'),
                    new OA\Property(property: 'errors',  type: 'object',  example: [
                        'end_time' => ['وقت النهاية يجب أن يكون بعد وقت البداية'],
                    ]),
                ])
            ),
            new OA\Response(
                response: 404,
                description: 'Schedule not found',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string',  example: 'الجلسة غير موجودة'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),        ]
    )]
    public function scheduleUpdate() {}

    #[OA\Delete(
        path: '/program/mosques/{mosque}/dawah_programs/{program}/schedules/{schedule}',
        operationId: 'deleteProgramSchedule',
        tags: ['Program Schedules'],
        summary: 'Delete a schedule entry',
        description: 'Permanently removes a single session from a program without affecting other sessions. **Auth:** `Bearer token` · **Role:** `mosque_manager`',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque',   in: 'path', required: true, description: 'Mosque ID',   schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'program',  in: 'path', required: true, description: 'Program ID',  schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'schedule', in: 'path', required: true, description: 'Schedule ID', schema: new OA\Schema(type: 'integer', example: 7)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schedule deleted successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'تم حذف الجلسة بنجاح'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(
                response: 404,
                description: 'Schedule not found',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string',  example: 'الجلسة غير موجودة'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'حدث خطأ في الخادم، يرجى المحاولة لاحقًا'),
                        new OA\Property(property: 'data', type: 'null', example: null),
                    ]
                )
            ),        ]
    )]
    public function scheduleDestroy() {}
}
