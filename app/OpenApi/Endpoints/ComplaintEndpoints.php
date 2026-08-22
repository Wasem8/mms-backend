<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class ComplaintEndpoints
{
    // =========================================================================
    //  Shared Schemas
    // =========================================================================

    #[OA\Schema(
        schema: 'ComplaintFile',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',        type: 'integer', example: 1),
            new OA\Property(property: 'file',      type: 'string',  example: 'https://xyz.supabase.co/storage/v1/object/public/mms/complaints/abc123.jpg'),
            new OA\Property(property: 'file_type', type: 'string',  example: 'image/jpeg'),
        ],
    )]
    public function schemaFile() {}

    #[OA\Schema(
        schema: 'ComplaintStatusLog',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',         type: 'integer',       example: 1),
            new OA\Property(property: 'old_status', type: 'string', nullable: true, enum: ['pending', 'in_progress', 'resolved', 'canceled'], example: null),
            new OA\Property(property: 'new_status', type: 'string', enum: ['pending', 'in_progress', 'resolved', 'canceled'], example: 'pending'),
            new OA\Property(property: 'note',       type: 'string', nullable: true, example: 'Complaint submitted and awaiting review'),
            new OA\Property(property: 'changed_at', type: 'string', example: '2026-05-13T09:00:00Z'),
        ],
    )]
    public function schemaStatusLog() {}

    #[OA\Schema(
        schema: 'Complaint',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',               type: 'integer', example: 12),
            new OA\Property(property: 'complaint_number', type: 'string',  example: 'CMP-2026-ABC123'),
            new OA\Property(property: 'title',            type: 'string',  example: 'Broken AC in prayer hall'),
            new OA\Property(property: 'description',      type: 'string',  example: 'The air conditioning has been out of service for two days.'),
            new OA\Property(property: 'status',           type: 'string',  enum: ['pending', 'in_progress', 'resolved', 'canceled'], example: 'pending'),
            new OA\Property(property: 'priority',         type: 'string',  enum: ['low', 'medium', 'high'], example: 'high'),
            new OA\Property(property: 'complaint_type',   type: 'string',  enum: ['service_missing', 'power_outage', 'corruption', 'employee_misconduct', 'technical_issue'], example: 'power_outage'),
            new OA\Property(property: 'email',            type: 'string',  nullable: true, example: 'user@example.com'),
            new OA\Property(property: 'is_anonymous',     type: 'boolean', example: false),
            new OA\Property(property: 'admin_notes',      type: 'string',  nullable: true, example: null),
            new OA\Property(property: 'assigned_admin_id', type: 'integer', nullable: true, example: null),
            new OA\Property(property: 'assigned_admin',    type: 'object',  nullable: true, properties: [
                new OA\Property(property: 'id',   type: 'integer', example: 3),
                new OA\Property(property: 'name', type: 'string',  example: 'Khalid Al-Otaibi'),
            ]),
            new OA\Property(property: 'mosque_id',        type: 'integer', example: 1),
            new OA\Property(property: 'mosque',           type: 'object',  nullable: true, properties: [
                new OA\Property(property: 'id',   type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string',  example: 'Al-Noor Mosque'),
            ]),
            new OA\Property(property: 'user', type: 'object', nullable: true, properties: [
                new OA\Property(property: 'id',    type: 'integer', example: 5),
                new OA\Property(property: 'name',  type: 'string',  example: 'Ahmed Hassan'),
                new OA\Property(property: 'email', type: 'string',  example: 'ahmed@example.com'),
            ]),
            new OA\Property(property: 'files', type: 'array', items: new OA\Items(ref: '#/components/schemas/ComplaintFile')),
            new OA\Property(property: 'status_logs', type: 'array', items: new OA\Items(ref: '#/components/schemas/ComplaintStatusLog')),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-13T08:30:00Z'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-13T09:30:00Z'),
        ],
    )]
    public function schemaComplaint() {}

    // =========================================================================
    //  GUEST / PUBLIC  —  No middleware
    // =========================================================================

    #[OA\Post(
        path: '/complaints/guest',
        operationId: 'submitComplaintGuest',
        tags: ['Complaints'],
        summary: 'Submit a complaint as a guest',
        description: 'Allows non-authenticated users to submit complaints. Returns the complaint object directly (not wrapped in ApiResponse).',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['title', 'description', 'mosque_id', 'complaint_type'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Broken AC in prayer hall'),
                        new OA\Property(property: 'description', type: 'string', example: 'The air conditioning has been out of service for two days and the prayer hall is extremely hot.'),
                        new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                        new OA\Property(property: 'complaint_type', type: 'string', enum: ['service_missing', 'power_outage', 'corruption', 'employee_misconduct', 'technical_issue'], example: 'power_outage'),
                        new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], example: 'high'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'is_anonymous', type: 'boolean', example: false),
                        new OA\Property(property: 'files[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary'), description: 'Optional attachments. Accepted: jpg, jpeg, png, pdf. Max 5 MB each.'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Complaint submitted successfully.',
                content: new OA\JsonContent(ref: '#/components/schemas/Complaint')
            ),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError')
        ]
    )]
    public function storeGuest() {}

    #[OA\Post(
        path: '/complaints/member',
        operationId: 'submitComplaintMember',
        tags: ['Complaints'],
        summary: 'Submit a complaint as a member',
        description: 'Allows authenticated users to submit complaints.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['title', 'description', 'mosque_id', 'complaint_type'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Broken AC in prayer hall'),
                        new OA\Property(property: 'description', type: 'string', example: 'The air conditioning has been out of service for two days and the prayer hall is extremely hot.'),
                        new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                        new OA\Property(property: 'complaint_type', type: 'string', enum: ['service_missing', 'power_outage', 'corruption', 'employee_misconduct', 'technical_issue'], example: 'power_outage'),
                        new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], example: 'high'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'is_anonymous', type: 'boolean', example: false),
                        new OA\Property(property: 'files[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary'), description: 'Optional attachments. Accepted: jpg, jpeg, png, pdf. Max 5 MB each.'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Complaint submitted successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaint submitted successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Complaint'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError')
        ]
    )]
    public function storeMember() {}

    #[OA\Get(
        path: '/complaints/track/{complaintNumber}',
        operationId: 'trackComplaint',
        tags: ['Complaints'],
        summary: 'Track complaint status by tracking number',
        description: 'Get the current status and history of a complaint using its tracking number.',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'complaintNumber',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'Unique complaint tracking number'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Complaint status retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaint status retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'complaint_number', type: 'string', example: 'CMP-2026-ABC123'),
                                new OA\Property(property: 'title', type: 'string', example: 'Broken AC in prayer hall'),
                                new OA\Property(property: 'current_status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'admin_resolution_note', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-13T08:30:00Z'),
                                new OA\Property(
                                    property: 'status_history',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                            new OA\Property(property: 'date', type: 'string', format: 'date-time', example: '2026-05-13T09:00:00Z'),
                                            new OA\Property(property: 'note', type: 'string', nullable: true, example: 'Complaint submitted and awaiting review'),
                                        ]
                                    )
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound')
        ]
    )]
    public function track() {}

    #[OA\Get(
        path: '/complaints/recent',
        operationId: 'getRecentComplaints',
        tags: ['Complaints'],
        summary: 'Get recent complaints for my mosque',
        description: 'Returns the latest complaints for the authenticated mosque_manager\'s mosque.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 5, minimum: 1, maximum: 50), description: 'Number of complaints to return'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recent complaints retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaints retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id',               type: 'integer', example: 12),
                                    new OA\Property(property: 'complaint_number', type: 'string',  example: 'CMP-2026-ABC123'),
                                    new OA\Property(property: 'title',            type: 'string',  example: 'Broken AC in prayer hall'),
                                    new OA\Property(property: 'status',           type: 'string',  enum: ['pending', 'in_progress', 'resolved', 'canceled'], example: 'pending'),
                                    new OA\Property(property: 'priority',         type: 'string',  enum: ['low', 'medium', 'high'], example: 'high'),
                                    new OA\Property(property: 'complaint_type',   type: 'string',  enum: ['service_missing', 'power_outage', 'corruption', 'employee_misconduct', 'technical_issue'], example: 'power_outage'),
                                    new OA\Property(property: 'is_anonymous',     type: 'boolean', example: false),
                                    new OA\Property(property: 'created_at',       type: 'string',  format: 'date-time', example: '2026-05-13T08:30:00Z'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),

            new OA\Response(
                response: 403,
                description: 'Forbidden — requires proper role (Access Denied)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Access denied. Your role does not allow this action.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function recentComplaints() {}

    #[OA\Get(
        path: '/complaints/stats',
        operationId: 'getComplaintPageStats',
        tags: ['Complaints'],
        summary: 'Complaint page stat cards',
        description: 'Returns the five stat cards for the authenticated mosque_manager\'s mosque: total, open, urgent, resolved this month, and average response time.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaints retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'total_complaints',
                                    type: 'integer',
                                    example: 142,
                                    description: 'إجمالي الشكاوى — all complaints for this mosque'
                                ),
                                new OA\Property(
                                    property: 'open_complaints',
                                    type: 'integer',
                                    example: 12,
                                    description: 'شكاوى مفتوحة — complaints with status pending or in_progress'
                                ),
                                new OA\Property(
                                    property: 'urgent_complaints',
                                    type: 'integer',
                                    example: 3,
                                    description: 'شكاوى عاجلة — open complaints with priority = high'
                                ),
                                new OA\Property(
                                    property: 'resolved_this_month',
                                    type: 'integer',
                                    example: 85,
                                    description: 'تم الحل (الشهر) — complaints resolved in the current month'
                                ),
                                new OA\Property(
                                    property: 'avg_response_hours',
                                    type: 'integer',
                                    example: 4,
                                    description: 'متوسط الاستجابة — average hours from complaint creation to first status change, rounded'
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),

            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),

            new OA\Response(
                response: 403,
                description: 'Forbidden — requires mosque_manager role (Access Denied)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Access denied. Your role does not allow this action.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function pageStats()
    {}

    #[OA\Get(
        path: '/admin/complaints',
        operationId: 'getAdminComplaints',
        tags: ['Complaints'],
        summary: 'List complaints for admin users',
        description: 'Retrieve complaints with optional filtering by status, mosque, type, and priority.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'resolved', 'canceled'])),
            new OA\Parameter(name: 'mosque_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'complaint_type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['service_missing', 'power_outage', 'corruption', 'employee_misconduct', 'technical_issue'])),
            new OA\Parameter(name: 'priority', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15), description: 'Items per page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Complaints retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaints retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Complaint')
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                                new OA\Property(property: 'has_more_pages', type: 'boolean', example: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/admin/complaints/{id}',
        operationId: 'getComplaintDetails',
        tags: ['Complaints'],
        summary: 'Get complaint details',
        description: 'Retrieve a single complaint and its related data for admin users.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Complaint details retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaint details retrieved successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Complaint'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function show() {}

    #[OA\Patch(
        path: '/admin/complaints/{id}/status',
        operationId: 'updateComplaintStatus',
        tags: ['Complaints'],
        summary: 'Update complaint status',
        description: 'Update the status and optional note for a specific complaint.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'resolved', 'canceled'], example: 'resolved'),
                    new OA\Property(property: 'note', type: 'string', nullable: true, example: 'Resolved after maintenance team fixed the issue.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Complaint status updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaint status updated successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Complaint'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function updateStatus() {}

    #[OA\Get(
        path: '/admin/complaints/statistics',
        operationId: 'getComplaintStatistics',
        tags: ['Complaints'],
        summary: 'Get complaint statistics',
        description: 'Retrieve aggregate complaint statistics for admin users.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'mosque_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'Filter statistics by mosque ID'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Complaint statistics retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaint statistics retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_complaints', type: 'integer', example: 45),
                                new OA\Property(
                                    property: 'by_status',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'pending', type: 'integer', example: 10),
                                        new OA\Property(property: 'in_progress', type: 'integer', example: 12),
                                        new OA\Property(property: 'resolved', type: 'integer', example: 18),
                                        new OA\Property(property: 'canceled', type: 'integer', example: 5),
                                    ]
                                ),
                                new OA\Property(property: 'by_type', type: 'object', example: ['service_missing' => 15, 'power_outage' => 10, 'corruption' => 8, 'employee_misconduct' => 6, 'technical_issue' => 6]),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function statistics() {}

    // ─── GET /admin/complaints/search ──────────────────────────────────────────

    #[OA\Get(
        path: '/admin/complaints/search',
        operationId: 'searchComplaints',
        tags: ['Complaints'],
        summary: 'Search complaints',
        description: 'Search complaints by keyword across complaint_number, title, description, and email.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string', minLength: 1), description: 'Search keyword'),
            new OA\Parameter(name: 'mosque_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'Filter by mosque (super_admin only)'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15), description: 'Items per page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search results retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaints retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Complaint')
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                                new OA\Property(property: 'has_more_pages', type: 'boolean', example: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function search() {}


    #[OA\Get(
        path: '/complaints/mine',
        operationId: 'getMyComplaints',
        tags: ['Complaints'],
        summary: 'List my complaints',
        description: 'Returns the authenticated member\'s own complaints, paginated. Guest complaints (submitted via /complaints/guest) never appear here since guests have no account — their only recovery path is the tracking number returned on submission.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'resolved', 'canceled']),
                description: 'Filter by complaint status'
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15),
                description: 'Items per page'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Complaints retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaints retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 41),
                                    new OA\Property(property: 'complaint_number', type: 'string', example: 'CMP-2026-000123'),
                                    new OA\Property(property: 'title', type: 'string', example: 'تسريب مياه في دورة المياه'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'resolved', 'canceled'], example: 'in_progress'),
                                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], example: 'high'),
                                    new OA\Property(property: 'complaint_type', type: 'string', enum: ['service_missing', 'power_outage', 'corruption', 'employee_misconduct', 'technical_issue'], example: 'technical_issue'),
                                    new OA\Property(
                                        property: 'mosque',
                                        type: 'object',
                                        nullable: true,
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 12),
                                            new OA\Property(property: 'name', type: 'string', example: 'جامع الرحمن'),
                                        ]
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2026-07-20 14:32'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 3),
                                new OA\Property(property: 'has_more_pages', type: 'boolean', example: false),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function mine() {}

    #[OA\Patch(
        path: '/admin/complaints/{id}/assign',
        operationId: 'assignComplaintToAdmin',
        tags: ['Complaints'],
        summary: 'Assign complaint to a super admin',
        description: 'Allows a mosque_manager to assign a complaint to a super_admin user for escalated handling. If admin_id is omitted, the complaint is assigned to the single super_admin automatically.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'admin_id', type: 'integer', nullable: true, example: 3, description: 'ID of the super_admin user receiving the complaint. If omitted, the complaint is assigned to the only super_admin automatically.'),
                    new OA\Property(property: 'note', type: 'string', nullable: true, example: 'Needs regional office review — recurring issue across branches.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Complaint assigned successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Complaint assigned successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Complaint'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function assignToAdmin() {}

}
