<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class VolunteerEndpoints
{
    // ─────────────────────────────────────────────
    //  OPPORTUNITIES — VOLUNTEER
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/opportunities',
        operationId: 'getVolunteerOpportunities',
        tags: ['Volunteer Opportunities'],
        summary: 'List all open volunteer opportunities',
        description: 'Returns a paginated list of all open volunteer opportunities. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'page',     in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Opportunities retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Opportunities retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'id',                   type: 'integer', example: 1),
                            new OA\Property(property: 'mosque_id',            type: 'integer', example: 2),
                            new OA\Property(property: 'title',                type: 'string',  example: 'تنظيم الصفوف'),
                            new OA\Property(property: 'description',          type: 'string',  nullable: true),
                            new OA\Property(property: 'required_volunteers',  type: 'integer', example: 10),
                            new OA\Property(property: 'status',               type: 'string',  enum: ['open', 'closed'], example: 'open'),
                            new OA\Property(property: 'start_date',           type: 'string',  format: 'date', example: '2026-07-01'),
                            new OA\Property(property: 'end_date',             type: 'string',  format: 'date', nullable: true, example: '2026-07-30'),
                            new OA\Property(property: 'created_at',           type: 'string',  format: 'date-time'),
                            new OA\Property(property: 'updated_at',           type: 'string',  format: 'date-time'),
                        ])
                    ),
                    new OA\Property(
                        property: 'pagination',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'current_page', type: 'integer', example: 1),
                            new OA\Property(property: 'last_page',    type: 'integer', example: 3),
                            new OA\Property(property: 'per_page',     type: 'integer', example: 15),
                            new OA\Property(property: 'total',        type: 'integer', example: 40),
                        ]
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function index() {}

    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/opportunities/{id}',
        operationId: 'getVolunteerOpportunity',
        tags: ['Volunteer Opportunities'],
        summary: 'Get a single volunteer opportunity',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Opportunity retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Opportunity retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id',                  type: 'integer', example: 1),
                            new OA\Property(property: 'mosque_id',           type: 'integer', example: 2),
                            new OA\Property(property: 'title',               type: 'string',  example: 'تنظيم الصفوف'),
                            new OA\Property(property: 'description',         type: 'string',  nullable: true),
                            new OA\Property(property: 'required_volunteers', type: 'integer', example: 10),
                            new OA\Property(property: 'status',              type: 'string',  enum: ['open', 'closed'], example: 'open'),
                            new OA\Property(property: 'start_date',          type: 'string',  format: 'date', example: '2026-07-01'),
                            new OA\Property(property: 'end_date',            type: 'string',  format: 'date', nullable: true),
                            new OA\Property(property: 'created_at',          type: 'string',  format: 'date-time'),
                            new OA\Property(property: 'updated_at',          type: 'string',  format: 'date-time'),
                        ]
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Opportunity not found'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function show() {}

    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/opportunities/{opportunityId}/apply',
        operationId: 'applyToVolunteerOpportunity',
        tags: ['Volunteer Opportunities'],
        summary: 'Apply to a volunteer opportunity',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'opportunityId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Application submitted successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Application submitted successfully'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Opportunity not found'),
            new OA\Response(response: 409, description: 'Already applied or opportunity is closed'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function apply() {}

    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/my-applications',
        operationId: 'getMyVolunteerApplications',
        tags: ['Volunteer Opportunities'],
        summary: 'List my volunteer applications',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'page',     in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Applications retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Applications retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'id',             type: 'integer', example: 5),
                            new OA\Property(property: 'volunteer_id',   type: 'integer', example: 12),
                            new OA\Property(property: 'opportunity_id', type: 'integer', example: 1),
                            new OA\Property(property: 'status',         type: 'string',  enum: ['pending', 'approved', 'rejected'], example: 'pending'),
                            new OA\Property(property: 'created_at',     type: 'string',  format: 'date-time'),
                        ])
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function myApplications() {}

    // ─────────────────────────────────────────────
    //  OPPORTUNITIES — MANAGER
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/manager/opportunities',
        operationId: 'getManagerVolunteerOpportunities',
        tags: ['Volunteer Opportunities'],
        summary: 'List opportunities managed by the authenticated manager',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'page',     in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Opportunities retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Opportunities retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'id',                  type: 'integer', example: 1),
                            new OA\Property(property: 'mosque_id',           type: 'integer', example: 2),
                            new OA\Property(property: 'title',               type: 'string',  example: 'تنظيم الصفوف'),
                            new OA\Property(property: 'description',         type: 'string',  nullable: true),
                            new OA\Property(property: 'required_volunteers', type: 'integer', example: 10),
                            new OA\Property(property: 'status',              type: 'string',  enum: ['open', 'closed'], example: 'open'),
                            new OA\Property(property: 'start_date',          type: 'string',  format: 'date', example: '2026-07-01'),
                            new OA\Property(property: 'end_date',            type: 'string',  format: 'date', nullable: true),
                            new OA\Property(property: 'created_at',          type: 'string',  format: 'date-time'),
                            new OA\Property(property: 'updated_at',          type: 'string',  format: 'date-time'),
                        ])
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — mosque_manager role required'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function managerIndex() {}

    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/opportunities',
        operationId: 'createVolunteerOpportunity',
        tags: ['Volunteer Opportunities'],
        summary: 'Create a new volunteer opportunity',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mosque_id', 'title', 'required_volunteers', 'start_date'],
                properties: [
                    new OA\Property(property: 'mosque_id',            type: 'integer', example: 1),
                    new OA\Property(property: 'title',                type: 'string',  example: 'تنظيم الصفوف'),
                    new OA\Property(property: 'description',          type: 'string',  nullable: true, example: 'نحتاج متطوعين لتنظيم صفوف المصلى'),
                    new OA\Property(property: 'required_volunteers',  type: 'integer', example: 10),
                    new OA\Property(property: 'start_date',           type: 'string',  format: 'date', example: '2026-07-01'),
                    new OA\Property(property: 'end_date',             type: 'string',  format: 'date', nullable: true, example: '2026-07-30'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Opportunity created successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Opportunity created successfully'),
                    new OA\Property(property: 'data',    type: 'object'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function store() {}

    // ─────────────────────────────────────────────

    #[OA\Put(
        path: '/volunteer/opportunities/{id}',
        operationId: 'updateVolunteerOpportunity',
        tags: ['Volunteer Opportunities'],
        summary: 'Update a volunteer opportunity',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'title',               type: 'string',  example: 'تنظيم الصفوف - محدّث'),
                new OA\Property(property: 'description',         type: 'string',  nullable: true),
                new OA\Property(property: 'required_volunteers', type: 'integer', example: 15),
                new OA\Property(property: 'start_date',          type: 'string',  format: 'date', example: '2026-07-05'),
                new OA\Property(property: 'end_date',            type: 'string',  format: 'date', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Opportunity updated successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Opportunity updated successfully'),
                    new OA\Property(property: 'data',    type: 'object'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Opportunity not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function update() {}

    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/opportunities/{id}/close',
        operationId: 'closeVolunteerOpportunity',
        tags: ['Volunteer Opportunities'],
        summary: 'Close a volunteer opportunity',
        description: 'Marks the opportunity as closed. No more applications will be accepted. Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Opportunity closed successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Opportunity closed successfully'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Opportunity not found'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function close() {}

    // ─────────────────────────────────────────────
    //  APPLICATIONS — MANAGER
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/opportunities/{opportunityId}/applications',
        operationId: 'getOpportunityApplications',
        tags: ['Volunteer Applications'],
        summary: 'List all applications for an opportunity',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'opportunityId', in: 'path',  required: true,  schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'page',          in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page',      in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Applications retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Applications retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'id',             type: 'integer', example: 5),
                            new OA\Property(property: 'volunteer_id',   type: 'integer', example: 12),
                            new OA\Property(property: 'opportunity_id', type: 'integer', example: 1),
                            new OA\Property(property: 'status',         type: 'string',  enum: ['pending', 'approved', 'rejected'], example: 'pending'),
                            new OA\Property(property: 'created_at',     type: 'string',  format: 'date-time'),
                        ])
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Opportunity not found'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function applications() {}

    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/applications/{applicationId}/approve',
        operationId: 'approveVolunteerApplication',
        tags: ['Volunteer Applications'],
        summary: 'Approve a volunteer application',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'applicationId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 5)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Application approved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Application approved successfully'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Application not found'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function approveApplication() {}

    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/applications/{applicationId}/reject',
        operationId: 'rejectVolunteerApplication',
        tags: ['Volunteer Applications'],
        summary: 'Reject a volunteer application',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'applicationId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 5)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Application rejected successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Application rejected successfully'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Application not found'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function rejectApplication() {}

    // ─────────────────────────────────────────────
    //  TASKS
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/applications/{applicationId}/tasks',
        operationId: 'getVolunteerTasks',
        tags: ['Volunteer Tasks'],
        summary: 'List tasks for an approved application',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'applicationId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 5)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tasks retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Tasks retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'id',               type: 'integer', example: 3),
                            new OA\Property(property: 'application_id',   type: 'integer', example: 5),
                            new OA\Property(property: 'task_description', type: 'string',  example: 'ترتيب صفوف المصلى قبل صلاة الجمعة'),
                            new OA\Property(property: 'status',           type: 'string',  enum: ['pending', 'completed'], example: 'pending'),
                            new OA\Property(property: 'created_at',       type: 'string',  format: 'date-time'),
                        ])
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Application not found'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function taskIndex() {}

    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/tasks',
        operationId: 'createVolunteerTask',
        tags: ['Volunteer Tasks'],
        summary: 'Assign a task to a volunteer',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['application_id', 'task_description'],
                properties: [
                    new OA\Property(property: 'application_id',   type: 'integer', example: 5),
                    new OA\Property(property: 'task_description', type: 'string',  example: 'ترتيب صفوف المصلى قبل صلاة الجمعة'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Task created successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Task created successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id',               type: 'integer', example: 3),
                            new OA\Property(property: 'application_id',   type: 'integer', example: 5),
                            new OA\Property(property: 'task_description', type: 'string',  example: 'ترتيب صفوف المصلى قبل صلاة الجمعة'),
                            new OA\Property(property: 'status',           type: 'string',  example: 'pending'),
                            new OA\Property(property: 'created_at',       type: 'string',  format: 'date-time'),
                        ]
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function taskStore() {}

    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/tasks/{taskId}/complete',
        operationId: 'completeVolunteerTask',
        tags: ['Volunteer Tasks'],
        summary: 'Mark a task as completed',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'taskId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 3)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task marked as completed',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Task completed successfully'),
                    new OA\Property(property: 'data',    type: 'null',    example: null),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function taskComplete() {}

    // ─────────────────────────────────────────────
    //  HOURS & EVALUATION
    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/logs',
        operationId: 'logVolunteerHours',
        tags: ['Volunteer Evaluation'],
        summary: 'Log volunteer hours',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['volunteer_id', 'opportunity_id', 'logged_hours'],
                properties: [
                    new OA\Property(property: 'volunteer_id',       type: 'integer', example: 12),
                    new OA\Property(property: 'opportunity_id',     type: 'integer', example: 1),
                    new OA\Property(property: 'logged_hours',       type: 'number',  format: 'float', example: 3.5),
                    new OA\Property(property: 'manager_evaluation', type: 'string',  nullable: true, example: 'ممتاز'),
                    new OA\Property(property: 'notes',              type: 'string',  nullable: true, example: 'أدى العمل بإتقان'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Hours logged successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Hours logged successfully'),
                    new OA\Property(property: 'data',    type: 'object'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function logHours() {}

    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/my-logs',
        operationId: 'getMyVolunteerLogs',
        tags: ['Volunteer Evaluation'],
        summary: 'Get my volunteer hour logs',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logs retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Logs retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'id',                 type: 'integer', example: 1),
                            new OA\Property(property: 'opportunity_id',     type: 'integer', example: 1),
                            new OA\Property(property: 'logged_hours',       type: 'number',  format: 'float', example: 3.5),
                            new OA\Property(property: 'manager_evaluation', type: 'string',  nullable: true),
                            new OA\Property(property: 'notes',              type: 'string',  nullable: true),
                            new OA\Property(property: 'created_at',         type: 'string',  format: 'date-time'),
                        ])
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function myLogs() {}

    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/hours/{volunteerId}/{opportunityId}',
        operationId: 'getTotalVolunteerHours',
        tags: ['Volunteer Evaluation'],
        summary: 'Get total logged hours for a volunteer in an opportunity',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'volunteerId',   in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 12)),
            new OA\Parameter(name: 'opportunityId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Total hours retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Total hours retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'volunteer_id',   type: 'integer', example: 12),
                            new OA\Property(property: 'opportunity_id', type: 'integer', example: 1),
                            new OA\Property(property: 'logged_hours',   type: 'number',  format: 'float', example: 12.5),
                        ]
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Volunteer or opportunity not found'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function totalHours() {}

    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/volunteer/certificates/{volunteerId}/{opportunityId}',
        operationId: 'issueVolunteerCertificate',
        tags: ['Volunteer Evaluation'],
        summary: 'Issue a certificate to a volunteer',
        description: 'Requires `mosque_manager` role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'volunteerId',   in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 12)),
            new OA\Parameter(name: 'opportunityId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Certificate issued successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Certificate issued successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id',              type: 'integer', example: 1),
                            new OA\Property(property: 'volunteer_id',    type: 'integer', example: 12),
                            new OA\Property(property: 'opportunity_id',  type: 'integer', example: 1),
                            new OA\Property(property: 'certificate_url', type: 'string',  nullable: true, example: 'https://storage.example.com/certs/cert_1.pdf'),
                            new OA\Property(property: 'issued_at',       type: 'string',  format: 'date-time'),
                        ]
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Volunteer or opportunity not found'),
            new OA\Response(response: 409, description: 'Certificate already issued'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function issueCertificate() {}

    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/volunteer/my-certificates',
        operationId: 'getMyVolunteerCertificates',
        tags: ['Volunteer Evaluation'],
        summary: 'Get my volunteer certificates',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Certificates retrieved successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status',  type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Certificates retrieved successfully'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'id',              type: 'integer', example: 1),
                            new OA\Property(property: 'volunteer_id',    type: 'integer', example: 12),
                            new OA\Property(property: 'opportunity_id',  type: 'integer', example: 1),
                            new OA\Property(property: 'certificate_url', type: 'string',  nullable: true, example: 'https://storage.example.com/certs/cert_1.pdf'),
                            new OA\Property(property: 'issued_at',       type: 'string',  format: 'date-time'),
                        ])
                    ),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function myCertificates() {}
}
