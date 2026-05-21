<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class MaintenanceRequestEndpoints
{

    #[OA\Get(
        path: '/maintenance',
        operationId: 'mosque.maintenanceRequests.index',
        tags: ['Maintenance Requests'],
        summary: 'List maintenance requests for a mosque',
        description: 'Returns a paginated list of maintenance requests scoped to a single mosque. Filterable by status.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by request status.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['pending', 'in_progress', 'completed', 'rejected'],
                    example: 'pending'
                )
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Number of results per page. Defaults to 15.',
                schema: new OA\Schema(type: 'integer', default: 15, example: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance requests retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance requests retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 42),
                                    new OA\Property(property: 'title', type: 'string', example: 'AC unit not cooling'),
                                    new OA\Property(property: 'description', type: 'string', example: 'The main hall AC has stopped cooling since yesterday.'),
                                    new OA\Property(
                                        property: 'category',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'value', type: 'string', example: 'hvac'),
                                            new OA\Property(property: 'label', type: 'string', example: 'HVAC'),
                                        ]
                                    ),
                                    new OA\Property(property: 'is_urgent', type: 'string', enum: ['low', 'medium', 'high', 'urgent'], example: 'high'),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'value', type: 'string', example: 'pending'),
                                            new OA\Property(property: 'label', type: 'string', example: 'Pending'),
                                        ]
                                    ),
                                    new OA\Property(property: 'rejection_reason', type: 'string', nullable: true, example: null),
                                    new OA\Property(
                                        property: 'attachments',
                                        type: 'array',
                                        items: new OA\Items(type: 'string'),
                                        example: ['uploads/ac-photo.jpg']
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', example: '21 May 2026, 10:30 AM'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '21 May 2026, 10:30 AM'),
                                    new OA\Property(
                                        property: 'mosque',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 7),
                                            new OA\Property(property: 'name', type: 'string', example: 'Al-Noor Mosque'),
                                        ]
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 3),
                                new OA\Property(property: 'last_page', type: 'integer', example: 1),
                                new OA\Property(property: 'has_more', type: 'boolean', example: false),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function index() {}

    // ───────────────────────────────────────────────────────────────────────

    #[OA\Post(
        path: '/maintenance',
        operationId: 'mosque.maintenanceRequests.store',
        tags: ['Maintenance Requests'],
        summary: 'Create a new maintenance request',
        description: 'Creates a maintenance request for the authenticated mosque manager\'s mosque. Status is always set to `pending` on creation.',
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
                    required: ['title', 'description', 'category'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'AC unit not cooling'),
                        new OA\Property(property: 'description', type: 'string', example: 'The main hall AC has stopped cooling since yesterday.'),
                        new OA\Property(
                            property: 'category',
                            type: 'string',
                            enum: ['hvac', 'electrical', 'plumbing', 'sound_system', 'general'],
                            example: 'hvac'
                        ),
                        new OA\Property(property: 'is_urgent', type: 'string', enum: ['low', 'medium', 'high', 'urgent'], default: 'low', example: 'low'),
                        new OA\Property(
                            property: 'attachments[]',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            description: 'Optional file attachments (images, documents).'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Maintenance request created successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request created successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 42),
                                new OA\Property(property: 'title', type: 'string', example: 'AC unit not cooling'),
                                new OA\Property(property: 'description', type: 'string', example: 'The main hall AC has stopped cooling since yesterday.'),
                                new OA\Property(
                                    property: 'category',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'value', type: 'string', example: 'hvac'),
                                        new OA\Property(property: 'label', type: 'string', example: 'HVAC'),
                                    ]
                                ),
                                new OA\Property(property: 'is_urgent', type: 'boolean', example: true),
                                new OA\Property(
                                    property: 'status',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'value', type: 'string', example: 'pending'),
                                        new OA\Property(property: 'label', type: 'string', example: 'Pending'),
                                    ]
                                ),
                                new OA\Property(property: 'rejection_reason', type: 'string', nullable: true, example: null),
                                new OA\Property(
                                    property: 'attachments[]',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['uploads/ac-photo.jpg']
                                ),
                                new OA\Property(property: 'created_at', type: 'string', example: '21 May 2026, 10:30 AM'),
                                new OA\Property(property: 'updated_at', type: 'string', example: '21 May 2026, 10:30 AM'),
                                new OA\Property(
                                    property: 'mosque',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 7),
                                        new OA\Property(property: 'name', type: 'string', example: 'Al-Noor Mosque'),
                                    ]
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function store() {}

    // ═══════════════════════════════════════════════════════════════════════
    //  ROLE 2 — Region Manager (Admin Panel)
    // ═══════════════════════════════════════════════════════════════════════

    #[OA\Get(
        path: '/maintenance/admin',
        operationId: 'admin.maintenanceRequests.index',
        tags: ['Maintenance Requests'],
        summary: 'List all maintenance requests across all mosques',
        description: 'Returns a paginated list of all maintenance requests. Filterable by status, category, and is_urgent. Requires the region_manager role.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['pending', 'in_progress', 'completed', 'rejected'],
                    example: 'pending'
                )
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['hvac', 'electrical', 'plumbing', 'sound_system', 'general'],
                    example: 'hvac'
                )
            ),
            new OA\Parameter(
                name: 'is_urgent',
                in: 'query',
                required: false,
                description: 'Filter by urgency level. Accepts low, medium, high, urgent.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['low', 'medium', 'high', 'urgent'],
                    example: 'high'
                )
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, example: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All maintenance requests retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'All maintenance requests retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 42),
                                    new OA\Property(property: 'title', type: 'string', example: 'AC unit not cooling'),
                                    new OA\Property(property: 'description', type: 'string', example: 'The main hall AC has stopped cooling since yesterday.'),
                                    new OA\Property(
                                        property: 'category',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'value', type: 'string', example: 'hvac'),
                                            new OA\Property(property: 'label', type: 'string', example: 'HVAC'),
                                        ]
                                    ),
                                    new OA\Property(property: 'is_urgent', type: 'string', enum: ['low', 'medium', 'high', 'urgent'], example: 'high'),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'value', type: 'string', example: 'pending'),
                                            new OA\Property(property: 'label', type: 'string', example: 'Pending'),
                                        ]
                                    ),
                                    new OA\Property(property: 'rejection_reason', type: 'string', nullable: true, example: null),
                                    new OA\Property(
                                        property: 'attachments',
                                        type: 'array',
                                        items: new OA\Items(type: 'string'),
                                        example: ['uploads/ac-photo.jpg']
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', example: '21 May 2026, 10:30 AM'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '21 May 2026, 10:30 AM'),
                                    new OA\Property(
                                        property: 'mosque',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 7),
                                            new OA\Property(property: 'name', type: 'string', example: 'Al-Noor Mosque'),
                                        ]
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 43),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'has_more', type: 'boolean', example: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function adminIndex() {}

    #[OA\Get(
        path: '/maintenance/track/{reference}',
        operationId: 'maintenance.track',
        tags: ['Maintenance Requests'],
        summary: 'Track a maintenance request by reference number',
        description: 'Retrieve a maintenance request using its reference number. Accessible to mosque managers (for their mosque) and super admins.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'reference',
                in: 'path',
                required: true,
                description: 'Public reference number of the maintenance request.',
                schema: new OA\Schema(type: 'string', example: 'MR-000123')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance request retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 42),
                                new OA\Property(property: 'reference_number', type: 'string', example: 'MR-000123'),
                                new OA\Property(property: 'title', type: 'string', example: 'AC unit not cooling'),
                                new OA\Property(property: 'description', type: 'string', example: 'The main hall AC has stopped cooling since yesterday.'),
                                new OA\Property(property: 'is_urgent', type: 'string', enum: ['low', 'medium', 'high', 'urgent'], example: 'high'),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'rejection_reason', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'attachments', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'created_at', type: 'string', example: '21 May 2026, 10:30 AM'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function track() {}

    // ───────────────────────────────────────────────────────────────────────

    #[OA\Put(
        path: '/maintenance/{id}/process',
        operationId: 'admin.maintenanceRequests.process',
        tags: ['Maintenance Requests'],
        summary: 'Process a maintenance request',
        description: 'Updates the status of a maintenance request. Automatically stamps the authenticated region manager\'s ID. A `rejection_reason` is required when setting status to `rejected`.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'The ID of the maintenance request.',
                schema: new OA\Schema(type: 'integer', example: 42)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['pending', 'in_progress', 'completed', 'rejected'],
                        example: 'rejected'
                    ),
                    new OA\Property(
                        property: 'rejection_reason',
                        type: 'string',
                        nullable: true,
                        description: 'Required when status is `rejected`.',
                        example: 'Duplicate request — already handled under ticket #39.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Maintenance request processed successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Maintenance request processed successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 42),
                                new OA\Property(property: 'title', type: 'string', example: 'AC unit not cooling'),
                                new OA\Property(property: 'description', type: 'string', example: 'The main hall AC has stopped cooling since yesterday.'),
                                new OA\Property(
                                    property: 'category',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'value', type: 'string', example: 'hvac'),
                                        new OA\Property(property: 'label', type: 'string', example: 'HVAC'),
                                    ]
                                ),
                                new OA\Property(property: 'is_urgent', type: 'boolean', example: true),
                                new OA\Property(
                                    property: 'status',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'value', type: 'string', example: 'rejected'),
                                        new OA\Property(property: 'label', type: 'string', example: 'Rejected'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'rejection_reason',
                                    type: 'string',
                                    nullable: true,
                                    example: 'Duplicate request — already handled under ticket #39.'
                                ),
                                new OA\Property(
                                    property: 'attachments',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['uploads/ac-photo.jpg']
                                ),
                                new OA\Property(property: 'created_at', type: 'string', example: '21 May 2026, 10:30 AM'),
                                new OA\Property(property: 'updated_at', type: 'string', example: '21 May 2026, 11:00 AM'),
                                new OA\Property(
                                    property: 'mosque',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 7),
                                        new OA\Property(property: 'name', type: 'string', example: 'Al-Noor Mosque'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'region_manager',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 3),
                                        new OA\Property(property: 'name', type: 'string', example: 'Ahmad Al-Farsi'),
                                    ]
                                ),
                            ]
                        ),
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
    public function process() {}
}
