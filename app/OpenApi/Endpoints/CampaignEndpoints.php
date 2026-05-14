<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class CampaignEndpoints
{
    // =========================================================================
    // Reusable schema — referenced by all endpoints via $ref
    // =========================================================================

    #[OA\Schema(
        schema: 'Campaign',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',               type: 'integer', example: 12),
            new OA\Property(property: 'mosque_id',        type: 'integer', example: 5),
            new OA\Property(property: 'title',            type: 'string',  example: 'صدقة جارية: حفر بئر ارتوازي'),
            new OA\Property(property: 'description',      type: 'string',  nullable: true),
            new OA\Property(property: 'target_amount',    type: 'number',  format: 'float', example: 1000000),
            new OA\Property(property: 'collected_amount', type: 'number',  format: 'float', example: 750000),
            new OA\Property(
                property: 'percent_complete',
                type: 'number',
                format: 'float',
                example: 75.0,
                description: 'UC-75 — computed: (collected_amount / target_amount) × 100'
            ),
            new OA\Property(
                property: 'status',
                type: 'string',
                enum: ['active', 'paused', 'completed', 'cancelled'],
                example: 'active'
            ),
            new OA\Property(
                property: 'priority',
                type: 'string',
                enum: ['high', 'medium', 'low'],
                example: 'high'
            ),
            new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-01-01'),
            new OA\Property(property: 'end_date',   type: 'string', format: 'date', nullable: true, example: '2026-06-01'),
            new OA\Property(
                property: 'days_remaining',
                type: 'integer',
                nullable: true,
                example: 12,
                description: 'null when no end_date is set. 0 when the campaign has ended.'
            ),
            new OA\Property(property: 'cover_image', type: 'string', nullable: true, example: 'https://…/storage/campaigns/abc.jpg'),
            new OA\Property(property: 'created_at',  type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at',  type: 'string', format: 'date-time'),
        ]
    )]
    public function schemaCampaign() {}

    // =========================================================================
    // GET /mosques/{mosqueId}/campaigns
    // Replaces: GET /campaign  +  GET /campaign/mosque/{mosqueId}
    // =========================================================================

    #[OA\Get(
        path: '/mosques/{mosqueId}/campaigns',
        operationId: 'listCampaigns',
        tags: ['Campaigns'],
        summary: 'List campaigns for a mosque',
        description: 'Returns all campaigns belonging to the given mosque, ordered newest first.',
        parameters: [
            new OA\Parameter(
                name: 'mosqueId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 5
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Campaign')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function listCampaigns() {}

    // =========================================================================
    // GET /mosques/{mosqueId}/campaigns/stats   ← NEW
    // =========================================================================

    #[OA\Get(
        path: '/mosques/{mosqueId}/campaigns/stats',
        operationId: 'getCampaignStats',
        tags: ['Campaigns'],
        summary: 'Dashboard stats for a mosque',
        description: 'Aggregated totals for the dashboard stats bar: total collected, active/completed counts, and month-on-month growth rate.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'mosqueId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 5
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'total_collected',
                                    type: 'number',
                                    format: 'float',
                                    example: 1250000,
                                    description: 'Sum of collected_amount across all mosque campaigns'
                                ),
                                new OA\Property(
                                    property: 'active_count',
                                    type: 'integer',
                                    example: 8,
                                    description: 'Campaigns with status = active'
                                ),
                                new OA\Property(
                                    property: 'completed_count',
                                    type: 'integer',
                                    example: 24,
                                    description: 'Campaigns with status = completed'
                                ),
                                new OA\Property(
                                    property: 'growth_rate_percent',
                                    type: 'number',
                                    format: 'float',
                                    example: 78.0,
                                    description: 'Month-on-month growth of collected_amount'
                                ),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getCampaignStats() {}

    // =========================================================================
    // GET /campaigns/{id}
    // =========================================================================

    #[OA\Get(
        path: '/campaigns/{id}',
        operationId: 'getCampaign',
        tags: ['Campaigns'],
        summary: 'Get a campaign',
        description: 'Retrieve a single campaign by ID. Includes computed percent_complete and days_remaining.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 12
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Campaign'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string',  example: 'Not found'),
                        new OA\Property(property: 'data',    nullable: true,  example: null),
                    ]
                )
            ),
        ]
    )]
    public function getCampaign() {}

    // =========================================================================
    // GET /campaigns/{id}/analytics   ← NEW
    // =========================================================================

    #[OA\Get(
        path: '/campaigns/{id}/analytics',
        operationId: 'getCampaignAnalytics',
        tags: ['Campaigns'],
        summary: 'Campaign analytics',
        description: 'The four metric cards on the campaign detail page: weekly growth %, average donation, unique donors, and total donation count.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 12
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'weekly_growth_percent',
                                    type: 'number',
                                    format: 'float',
                                    example: 12.0,
                                    description: 'Change in cash donations vs same period last week'
                                ),
                                new OA\Property(
                                    property: 'avg_donation_amount',
                                    type: 'integer',
                                    example: 604,
                                    description: 'Average completed cash donation amount (rounded)'
                                ),
                                new OA\Property(
                                    property: 'unique_donors_count',
                                    type: 'integer',
                                    example: 850,
                                    description: 'Distinct donor_name values on completed donations'
                                ),
                                new OA\Property(
                                    property: 'total_donations_count',
                                    type: 'integer',
                                    example: 1240,
                                    description: 'Total completed cash donation records'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function getCampaignAnalytics() {}

    // =========================================================================
    // POST /campaigns
    // =========================================================================

    #[OA\Post(
        path: '/campaigns',
        operationId: 'createCampaign',
        tags: ['Campaigns'],
        summary: 'Create a campaign',
        description: 'Creates a new donation campaign. Send as multipart/form-data when attaching a cover image.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['mosque_id', 'title', 'target_amount', 'start_date'],
                    properties: [
                        new OA\Property(property: 'mosque_id',     type: 'integer', example: 5),
                        new OA\Property(property: 'title',         type: 'string',  example: 'كسوة العيد للأيتام'),
                        new OA\Property(property: 'description',   type: 'string',  nullable: true),
                        new OA\Property(property: 'target_amount', type: 'number',  format: 'float', example: 25000),
                        new OA\Property(property: 'start_date',    type: 'string',  format: 'date',  example: '2026-05-01'),
                        new OA\Property(property: 'end_date',      type: 'string',  format: 'date',  nullable: true, example: '2026-06-01'),
                        new OA\Property(
                            property: 'priority',
                            type: 'string',
                            enum: ['high', 'medium', 'low'],
                            example: 'medium'
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            enum: ['active', 'paused', 'completed', 'cancelled'],
                            example: 'active'
                        ),
                        new OA\Property(
                            property: 'cover_image',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Campaign created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Campaign created successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Campaign'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function createCampaign() {}

    // =========================================================================
    // PUT /campaigns/{id}
    // Declared as OA\Post with _method spoofing to support multipart + file
    // =========================================================================

    #[OA\Post(
        path: '/campaigns/{id}',
        operationId: 'updateCampaign',
        tags: ['Campaigns'],
        summary: 'Update a campaign',
        description: 'Partial update. Send as multipart/form-data when replacing the cover image. Add `_method=PUT` for clients that do not support PUT with multipart.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 12
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: '_method',
                            type: 'string',
                            example: 'PUT',
                            description: 'Method spoofing — required for multipart PUT'
                        ),
                        new OA\Property(property: 'title',         type: 'string', nullable: true),
                        new OA\Property(property: 'description',   type: 'string', nullable: true),
                        new OA\Property(property: 'target_amount', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'start_date',    type: 'string', format: 'date',  nullable: true),
                        new OA\Property(property: 'end_date',      type: 'string', format: 'date',  nullable: true),
                        new OA\Property(
                            property: 'priority',
                            type: 'string',
                            enum: ['high', 'medium', 'low'],
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            enum: ['active', 'paused', 'completed', 'cancelled'],
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'cover_image',
                            type: 'string',
                            format: 'binary',
                            nullable: true,
                            description: 'Replaces the existing image; old file is deleted from Supabase.'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Campaign updated successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Campaign'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function updateCampaign() {}

    // =========================================================================
    // DELETE /campaigns/{id}
    // =========================================================================

    #[OA\Delete(
        path: '/campaigns/{id}',
        operationId: 'deleteCampaign',
        tags: ['Campaigns'],
        summary: 'Delete a campaign',
        description: 'Permanently deletes the campaign and removes its cover image from Supabase storage.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 12
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Campaign deleted.'),
                        new OA\Property(property: 'data',    nullable: true,  example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — requires mosque_manager role'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function deleteCampaign() {}
}
