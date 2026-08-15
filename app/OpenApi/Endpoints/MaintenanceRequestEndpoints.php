<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class MaintenanceRequestEndpoints
{
    // =========================================================================
    //  Shared Schemas
    // =========================================================================

    #[OA\Schema(
        schema: 'MaintenanceFile',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',        type: 'integer', example: 1),
            new OA\Property(property: 'file_name', type: 'string',  example: 'ac-photo.jpg'),
            new OA\Property(property: 'file_path', type: 'string',  example: 'https://xyz.supabase.co/storage/v1/object/public/maintenance/ac-photo.jpg'),
            new OA\Property(property: 'file_type', type: 'string',  example: 'image/jpeg'),
            new OA\Property(property: 'file_size', type: 'integer', example: 204800, description: 'Size in bytes'),
        ],
    )]
    public function schemaFile() {}

    #[OA\Schema(
        schema: 'MaintenanceStatusLog',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',         type: 'integer',       example: 1),
            new OA\Property(property: 'old_status', type: 'string', nullable: true, enum: ['pending', 'in_progress', 'completed', 'cancelled'], example: null),
            new OA\Property(property: 'new_status', type: 'string', enum: ['pending', 'in_progress', 'completed', 'cancelled'], example: 'pending'),
            new OA\Property(property: 'changed_by', type: 'string', example: 'Ahmed Hassan'),
            new OA\Property(property: 'notes',      type: 'string', nullable: true, example: 'Request created.'),
            new OA\Property(property: 'created_at', type: 'string', example: '21 May 2026, 10:30 AM'),
        ],
    )]
    public function schemaStatusLog() {}

    #[OA\Schema(
        schema: 'MaintenanceRequest',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',                 type: 'integer', example: 42),
            new OA\Property(property: 'maintenance_number', type: 'string',  example: 'MR-2026-AB1C2D'),
            new OA\Property(property: 'mosque_id',          type: 'integer', example: 7),
            new OA\Property(property: 'title',              type: 'string',  example: 'AC unit not cooling'),
            new OA\Property(property: 'description',        type: 'string',  example: 'The main hall AC has stopped cooling since yesterday.'),
            new OA\Property(property: 'category',           type: 'string',  enum: ['electrical', 'plumbing', 'carpentry', 'cleaning', 'other'],  example: 'electrical'),
            new OA\Property(property: 'priority',           type: 'string',  enum: ['low', 'medium', 'high', 'urgent'], example: 'high'),
            new OA\Property(property: 'status',             type: 'string',  enum: ['pending', 'in_progress', 'completed', 'cancelled'],          example: 'pending'),
            new OA\Property(
                property: 'requested_by',
                type: 'object',
                description: 'Automatically resolved from the authenticated user\'s token. Not accepted as input.',
                properties: [
                    new OA\Property(property: 'id',   type: 'integer', example: 12),
                    new OA\Property(property: 'name', type: 'string',  example: 'Ahmed Hassan'),
                ],
            ),
            new OA\Property(property: 'scheduled_at', type: 'string',  nullable: true, example: '01 Jun 2026, 09:00 AM'),
            new OA\Property(property: 'completed_at', type: 'string',  nullable: true, example: null),
            new OA\Property(property: 'notes',        type: 'string',  nullable: true, example: 'Please come after Fajr prayer.'),
            new OA\Property(property: 'files_requested',     type: 'boolean', default: false, example: true, description: 'Whether the region manager requested additional files from the mosque manager.'),
            new OA\Property(
                property: 'files_requested_by',
                type: 'object',
                nullable: true,
                description: 'Region manager (super_admin) who requested the additional files.',
                properties: [
                    new OA\Property(property: 'id',   type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string',  example: 'Dr. Abdullah'),
                ],
            ),
            new OA\Property(property: 'files_requested_at',  type: 'string', nullable: true, format: 'date-time', example: '2026-08-15T10:00:00Z'),
            new OA\Property(property: 'files_request_note',  type: 'string', nullable: true, example: 'Please upload photos of the damaged AC unit.'),
            new OA\Property(
                property: 'files',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/MaintenanceFile'),
            ),
            new OA\Property(
                property: 'status_logs',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/MaintenanceStatusLog'),
            ),
            new OA\Property(
                property: 'mosque',
                type: 'object',
                nullable: true,
                properties: [
                    new OA\Property(property: 'id',   type: 'integer', example: 7),
                    new OA\Property(property: 'name', type: 'string',  example: 'Al-Noor Mosque'),
                ],
            ),
            new OA\Property(property: 'created_at', type: 'string', example: '21 May 2026, 10:30 AM'),
            new OA\Property(property: 'updated_at', type: 'string', example: '21 May 2026, 10:30 AM'),
        ],
    )]
    public function schemaRequest() {}
    #[OA\Schema(
        schema: 'PublicMaintenanceRequest',
        type: 'object',
        description: 'Reduced-visibility version of MaintenanceRequest for guest/public access — excludes requester identity, internal notes, and status change history.',
        properties: [
            new OA\Property(property: 'id',                 type: 'integer', example: 42),
            new OA\Property(property: 'maintenance_number', type: 'string',  example: 'MR-2026-AB1C2D'),
            new OA\Property(property: 'title',              type: 'string',  example: 'AC unit not cooling'),
            new OA\Property(property: 'description',        type: 'string',  example: 'The main hall AC has stopped cooling since yesterday.'),
            new OA\Property(property: 'category',           type: 'string',  enum: ['electrical', 'plumbing', 'carpentry', 'cleaning', 'other'], example: 'electrical'),
            new OA\Property(property: 'priority',           type: 'string',  enum: ['low', 'medium', 'high', 'urgent'], example: 'high'),
            new OA\Property(property: 'status',             type: 'string',  enum: ['pending', 'in_progress', 'completed', 'cancelled'], example: 'pending'),
            new OA\Property(property: 'scheduled_at',       type: 'string',  nullable: true, example: '01 Jun 2026, 09:00 AM'),
            new OA\Property(property: 'completed_at',       type: 'string',  nullable: true, example: null),
            new OA\Property(
                property: 'files',
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'file_path', type: 'string', example: 'https://xyz.supabase.co/storage/v1/object/public/maintenance/ac-photo.jpg'),
                        new OA\Property(property: 'file_name', type: 'string', example: 'ac-photo.jpg'),
                    ]
                ),
            ),
            new OA\Property(
                property: 'mosque',
                type: 'object',
                nullable: true,
                properties: [
                    new OA\Property(property: 'id',   type: 'integer', example: 7),
                    new OA\Property(property: 'name', type: 'string',  example: 'Al-Noor Mosque'),
                ],
            ),
            new OA\Property(property: 'created_at', type: 'string', example: '21 May 2026, 10:30 AM'),
        ],
    )]
    public function schemaPublicRequest() {}


    // =========================================================================
    //  MOSQUE MANAGER  —  middleware: auth:api, role:mosque_manager
    //  Prefix: /maintenance
    // =========================================================================

    // ─── GET /maintenance ─────────────────────────────────────────────────────

    #[OA\Get(
        path: '/maintenance',
        operationId: 'maintenance.index',
        tags: ['Maintenance Requests'],
        summary: 'List maintenance requests',
        description: 'Returns a paginated list of maintenance requests scoped to the authenticated mosque manager\'s mosque. Filterable by status and priority.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'completed', 'cancelled'], example: 'pending'),
            ),
            new OA\Parameter(
                name: 'priority',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent'], example: 'high'),
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, example: 15),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance requests retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance requests retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/MaintenanceRequest'),
                                ),
                                new OA\Property(
                                    property: 'pagination',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                        new OA\Property(property: 'per_page',     type: 'integer', example: 15),
                                        new OA\Property(property: 'total',        type: 'integer', example: 8),
                                        new OA\Property(property: 'last_page',    type: 'integer', example: 1),
                                        new OA\Property(property: 'has_more',     type: 'boolean', example: false),
                                    ],
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ],
    )]
    public function index() {}

    // ─── POST /maintenance ────────────────────────────────────────────────────

    #[OA\Post(
        path: '/maintenance',
        operationId: 'maintenance.store',
        tags: ['Maintenance Requests'],
        summary: 'Submit a new maintenance request',
        description: 'Creates a maintenance request for the authenticated mosque manager\'s mosque. `mosque_id` is automatically resolved from the manager\'s token and cannot be set manually. A unique `maintenance_number` (MR-YYYY-XXXXXX) is auto-generated. Status defaults to `pending`. `requested_by` is automatically resolved from the authenticated user\'s token.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'description', 'category'],
                    properties: [
                        new OA\Property(property: 'title',        type: 'string',  maxLength: 255,  example: 'AC unit not cooling'),
                        new OA\Property(property: 'description',  type: 'string',  maxLength: 5000, example: 'The main hall AC has stopped cooling since yesterday.'),
                        new OA\Property(property: 'category',     type: 'string',  enum: ['electrical', 'plumbing', 'carpentry', 'cleaning', 'other'], example: 'electrical'),
                        new OA\Property(property: 'priority',     type: 'string',  enum: ['low', 'medium', 'high', 'urgent'], default: 'medium', example: 'high'),
                        new OA\Property(property: 'scheduled_at', type: 'string',  format: 'date-time', nullable: true, example: '2026-06-01T09:00:00Z'),
                        new OA\Property(property: 'notes',        type: 'string',  nullable: true, example: 'Please come after Fajr prayer.'),
                        new OA\Property(
                            property: 'files[]',
                            type: 'array',
                            description: 'Optional attachments. Accepted: jpg, jpeg, png, pdf, doc, docx. Max 10 MB each. Up to 10 files.',
                            items: new OA\Items(type: 'string', format: 'binary'),
                        ),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Maintenance request submitted successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request submitted successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MaintenanceRequest'),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function store() {}
    // ─── GET /maintenance/{id} ────────────────────────────────────────────────

    #[OA\Get(
        path: '/maintenance/{id}',
        operationId: 'maintenance.show',
        tags: ['Maintenance Requests'],
        summary: 'Get a maintenance request',
        description: 'Returns the details of a single maintenance request. Mosque managers can only access requests belonging to their own mosque.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 42)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance request retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request retrieved successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MaintenanceRequest'),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function show() {}

    #[OA\Get(
        path: '/maintenance/stats',
        operationId: 'getMaintenancePageStats',
        tags: ['Maintenance Requests'],
        summary: 'Maintenance page stat cards',
        description: 'Returns the four stat cards for the authenticated mosque_manager\'s mosque: open requests, in progress, completed this month, and critical failures.',
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
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance page stats retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'open_requests',
                                    type: 'integer',
                                    example: 14,
                                    description: 'طلبات مفتوحة — requests with status = pending'
                                ),
                                new OA\Property(
                                    property: 'in_progress',
                                    type: 'integer',
                                    example: 5,
                                    description: 'جاري العمل — requests with status = in_progress'
                                ),
                                new OA\Property(
                                    property: 'completed_this_month',
                                    type: 'integer',
                                    example: 42,
                                    description: 'تم إنجازها (الشهر) — requests completed in the current month'
                                ),
                                new OA\Property(
                                    property: 'critical',
                                    type: 'integer',
                                    example: 1,
                                    description: 'أعطال حرجة — open or in-progress requests with priority = urgent'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function pageStats() {}

    #[OA\Get(
        path: '/maintenance/recent',
        operationId: 'getRecentMaintenanceRequests',
        tags: ['Maintenance Requests'],
        summary: 'Get recent maintenance requests for my mosque',
        description: 'Returns the latest maintenance requests for the authenticated mosque_manager\'s mosque.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 5, minimum: 1, maximum: 50), description: 'Number of requests to return'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recent maintenance requests retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id',                   type: 'integer', example: 7),
                                    new OA\Property(property: 'maintenance_number',   type: 'string',  example: 'MR-2026-ABC123'),
                                    new OA\Property(property: 'title',                type: 'string',  example: 'Fix broken window'),
                                    new OA\Property(property: 'status',               type: 'string',  enum: ['pending', 'in_progress', 'completed', 'cancelled'], example: 'pending'),
                                    new OA\Property(property: 'priority',             type: 'string',  enum: ['low', 'medium', 'high'], example: 'high'),
                                    new OA\Property(property: 'category',             type: 'string',  example: 'electrical'),
                                    new OA\Property(property: 'created_at',           type: 'string',  format: 'date-time', example: '2026-05-13T08:30:00Z'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — requires mosque_manager role'),
        ]
    )]
    public function recentRequests() {}

    // ─── GET /maintenance/search ───────────────────────────────────────────────

    #[OA\Get(
        path: '/maintenance/search',
        operationId: 'searchMaintenanceRequests',
        tags: ['Maintenance Requests'],
        summary: 'Search maintenance requests',
        description: 'Search maintenance requests by keyword across maintenance_number, title, and description.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string', minLength: 1), description: 'Search keyword'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15, minimum: 1, maximum: 100), description: 'Items per page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search results retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Search results retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/MaintenanceRequest'),
                                ),
                                new OA\Property(
                                    property: 'pagination',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                        new OA\Property(property: 'per_page',     type: 'integer', example: 15),
                                        new OA\Property(property: 'total',        type: 'integer', example: 8),
                                        new OA\Property(property: 'last_page',    type: 'integer', example: 1),
                                        new OA\Property(property: 'has_more',     type: 'boolean', example: false),
                                    ],
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function search() {}

    // ─── PUT /maintenance/{id} ────────────────────────────────────────────────

    #[OA\Put(
        path: '/maintenance/{id}',
        operationId: 'maintenance.update',
        tags: ['Maintenance Requests'],
        summary: 'Update a maintenance request',
        description: 'Updates editable fields of a maintenance request. All fields are optional — only send what needs to change.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 42)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title',        type: 'string', maxLength: 255,  example: 'AC unit not cooling — urgent'),
                        new OA\Property(property: 'description',  type: 'string', maxLength: 5000, example: 'Updated description.'),
                        new OA\Property(property: 'category',     type: 'string', enum: ['electrical', 'plumbing', 'carpentry', 'cleaning', 'other'], example: 'plumbing'),
                        new OA\Property(property: 'priority',     type: 'string', enum: ['low', 'medium', 'high', 'urgent'], example: 'urgent'),
                        new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time', nullable: true, example: '2026-06-05T08:00:00Z'),
                        new OA\Property(property: 'notes',        type: 'string', nullable: true, example: 'Updated note.'),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance request updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request updated successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MaintenanceRequest'),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function update() {}

    // ─── DELETE /maintenance/{id} ─────────────────────────────────────────────

    #[OA\Delete(
        path: '/maintenance/{id}',
        operationId: 'maintenance.destroy',
        tags: ['Maintenance Requests'],
        summary: 'Delete a maintenance request',
        description: 'Soft-deletes the maintenance request and removes all attached files from Supabase storage.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 42)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance request deleted successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request deleted successfully.'),
                        new OA\Property(property: 'data',    nullable: true, example: null),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function destroy() {}

    // ─── GET /maintenance/track/{maintenance_number} ──────────────────────────

    #[OA\Get(
        path: '/maintenance/track/{maintenance_number}',
        operationId: 'maintenance.track',
        tags: ['Maintenance Requests'],
        summary: 'Track a request by maintenance number',
        description: 'Returns the maintenance request and its full status history using the public `maintenance_number` (e.g. MR-2026-AB1C2D).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'maintenance_number',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'MR-2026-AB1C2D'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance request retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request retrieved successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MaintenanceRequest'),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function track() {}

    // ─── GET /maintenance/file-requests ───────────────────────────────────────

    #[OA\Get(
        path: '/maintenance/file-requests',
        operationId: 'maintenance.fileRequests',
        tags: ['Maintenance Requests'],
        summary: 'List requests awaiting additional files',
        description: 'Returns a paginated list of maintenance requests for the authenticated mosque manager\'s mosque where the region manager (super_admin) has requested additional files that have not been uploaded yet.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, minimum: 1, maximum: 100),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'File requests retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'File requests retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/MaintenanceRequest'),
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page',     type: 'integer', example: 15),
                                new OA\Property(property: 'total',        type: 'integer', example: 3),
                                new OA\Property(property: 'last_page',    type: 'integer', example: 1),
                                new OA\Property(property: 'has_more_pages', type: 'boolean', example: false),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ],
    )]
    public function fileRequests() {}

    // ─── POST /maintenance/{id}/upload-files ──────────────────────────────────

    #[OA\Post(
        path: '/maintenance/{id}/upload-files',
        operationId: 'maintenance.uploadFiles',
        tags: ['Maintenance Requests'],
        summary: 'Upload additional files requested by the region manager',
        description: 'Uploads the additional files requested by the region manager for a maintenance request. Only allowed when `files_requested` is `true` and the request belongs to the authenticated mosque manager\'s mosque. Uploading clears the file request.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 42)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['files'],
                    properties: [
                        new OA\Property(
                            property: 'files[]',
                            type: 'array',
                            description: 'Additional attachments. Accepted: jpg, jpeg, png, pdf, doc, docx. Max 10 MB each. Up to 10 files.',
                            items: new OA\Items(type: 'string', format: 'binary'),
                        ),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Additional files uploaded successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Additional files uploaded successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MaintenanceRequest'),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function uploadFiles() {}

    // =========================================================================
    //  SUPER ADMIN  —  middleware: auth:api, role:super_admin
    //  Prefix: /maintenance/admin
    // =========================================================================

    // ─── GET /maintenance/admin ───────────────────────────────────────────────

    #[OA\Get(
        path: '/maintenance/admin',
        operationId: 'admin.maintenance.index',
        tags: ['Maintenance Requests — Admin'],
        summary: 'List all maintenance requests (admin)',
        description: 'Returns a paginated list of all maintenance requests across all mosques. Filterable by status, category, and priority.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'completed', 'cancelled'], example: 'pending'),
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['electrical', 'plumbing', 'carpentry', 'cleaning', 'other'], example: 'electrical'),
            ),
            new OA\Parameter(
                name: 'priority',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent'], example: 'urgent'),
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, example: 15),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All maintenance requests retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'All maintenance requests retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/MaintenanceRequest'),
                                ),
                                new OA\Property(
                                    property: 'pagination',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                        new OA\Property(property: 'per_page',     type: 'integer', example: 15),
                                        new OA\Property(property: 'total',        type: 'integer', example: 43),
                                        new OA\Property(property: 'last_page',    type: 'integer', example: 3),
                                        new OA\Property(property: 'has_more',     type: 'boolean', example: true),
                                    ],
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ],
    )]
    public function adminIndex() {}

    // ─── PUT /maintenance/admin/{id} ──────────────────────────────────────────

    #[OA\Put(
        path: '/maintenance/admin/{id}',
        operationId: 'admin.maintenance.process',
        tags: ['Maintenance Requests — Admin'],
        summary: 'Process a maintenance request',
        description: 'Updates the status of a maintenance request. Cannot be set back to `pending`. A `notes` field is required when setting status to `cancelled`.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 42)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['status'],
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            enum: ['in_progress', 'completed', 'cancelled'],
                            example: 'in_progress',
                            description: 'Cannot be set back to `pending`.',
                        ),
                        new OA\Property(
                            property: 'notes',
                            type: 'string',
                            nullable: true,
                            example: 'Duplicate — already handled under MR-2026-XY3Z4W.',
                            description: 'Required when status is `cancelled`.',
                        ),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance request processed successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request processed successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MaintenanceRequest'),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function process() {}

    // ─── POST /maintenance/admin/{id}/request-files ──────────────────────────

    #[OA\Post(
        path: '/maintenance/admin/{id}/request-files',
        operationId: 'admin.maintenance.requestFiles',
        tags: ['Maintenance Requests — Admin'],
        summary: 'Request additional files from the mosque manager',
        description: 'Lets the region manager (super_admin) request additional files from the mosque manager for a maintenance request. Sets `files_requested` to `true`. Cannot be used on `completed` or `cancelled` requests.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 42)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['note'],
                    properties: [
                        new OA\Property(
                            property: 'note',
                            type: 'string',
                            maxLength: 1000,
                            example: 'Please upload photos of the damaged AC unit.',
                            description: 'Reason the additional files are required.',
                        ),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Additional files requested successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Additional files requested successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MaintenanceRequest'),
                    ],
                ),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ],
    )]
    public function adminRequestFiles() {}

    #[OA\Get(
        path: '/maintenance/public',
        operationId: 'maintenance.public.index',
        tags: ['Maintenance Requests — Public'],
        summary: 'List maintenance requests (public)',
        description: 'Returns a paginated list of maintenance requests visible to guests, with sensitive fields (requester identity, internal notes, status change history) excluded. Filterable by status, category, priority, and mosque.',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'completed', 'cancelled'], example: 'pending'),
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['electrical', 'plumbing', 'carpentry', 'cleaning', 'other'], example: 'electrical'),
            ),
            new OA\Parameter(
                name: 'priority',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent'], example: 'high'),
            ),
            new OA\Parameter(
                name: 'mosque_id',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 7),
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, example: 15),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance requests retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance requests retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/PublicMaintenanceRequest'),
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page',     type: 'integer', example: 15),
                                new OA\Property(property: 'total',        type: 'integer', example: 8),
                                new OA\Property(property: 'last_page',    type: 'integer', example: 1),
                                new OA\Property(property: 'has_more_pages', type: 'boolean', example: false),
                            ],
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function publicIndex() {}

    // ─── GET /maintenance/public/{id} ───────────────────────────────────────

    #[OA\Get(
        path: '/maintenance/public/{id}',
        operationId: 'maintenance.public.show',
        tags: ['Maintenance Requests — Public'],
        summary: 'Get a maintenance request (public)',
        description: 'Returns the public-safe details of a single maintenance request — sensitive fields excluded.',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 42)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance request retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request retrieved successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PublicMaintenanceRequest'),
                    ],
                ),
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ],
    )]
    public function publicShow() {}
}
