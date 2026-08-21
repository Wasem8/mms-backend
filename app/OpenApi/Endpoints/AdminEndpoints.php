<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

/**
 * Admin / Dashboard endpoints (super_admin).
 */
class AdminEndpoints
{
    // =========================================================================
    // GET /admin/activity-log
    // Super-admin activity feed — derived from existing tables (no new storage)
    // =========================================================================

    #[OA\Get(
        path: '/api/admin/activity-log',
        operationId: 'adminActivityLog',
        tags: ['Admin'],
        summary: 'Super-admin activity log',
        description: <<<DESC
        Returns a unified activity feed for the authenticated super-admin, aggregated from existing
        tables that record the actor (no dedicated activity-log storage):
        - complaint status changes (`complaint_status_logs`)
        - sermon approvals (`sermons.region_manager_id`)
        - invitations created (`invitations.created_by`)
        - maintenance status changes (ALL requests; actor = the request's mosque manager via `manager_id`)
        Results are sorted by time (newest first) and paginated.
        DESC,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'module',    in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['complaints', 'sermons', 'invitations', 'maintenance']), description: 'Filter by source module'),
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'), description: 'Filter activities on or after this date'),
            new OA\Parameter(name: 'date_to',   in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'), description: 'Filter activities on or before this date'),
            new OA\Parameter(name: 'per_page',  in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15, minimum: 1, maximum: 100)),
            new OA\Parameter(name: 'page',      in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)),
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
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'module',      type: 'string', example: 'complaints'),
                                    new OA\Property(property: 'action_key',  type: 'string', example: 'complaint_status_changed'),
                                    new OA\Property(property: 'description', type: 'string', example: 'تغيير حالة الشكوى رقم #12 من pending إلى resolved'),
                                    new OA\Property(property: 'target_type', type: 'string', example: 'complaint'),
                                    new OA\Property(property: 'target_id',   type: 'integer', example: 12),
                                    new OA\Property(property: 'actor_id',    type: 'integer', nullable: true, example: 1),
                                    new OA\Property(property: 'actor_name',  type: 'string', example: 'Admin User'),
                                    new OA\Property(property: 'created_at',  type: 'string', format: 'date-time', example: '2026-08-15 09:05:23'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page',    type: 'integer', example: 4),
                                new OA\Property(property: 'per_page',     type: 'integer', example: 15),
                                new OA\Property(property: 'total',        type: 'integer', example: 56),
                                new OA\Property(property: 'from',         type: 'integer', example: 1),
                                new OA\Property(property: 'to',           type: 'integer', example: 15),
                            ]
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', example: 'http://localhost:8000/api/admin/activity-log?page=1'),
                                new OA\Property(property: 'last',  type: 'string', example: 'http://localhost:8000/api/admin/activity-log?page=4'),
                                new OA\Property(property: 'prev',  type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'next',  type: 'string', nullable: true, example: 'http://localhost:8000/api/admin/activity-log?page=2'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — super_admin role required'),
        ]
    )]
    public function adminActivityLog() {}
}
