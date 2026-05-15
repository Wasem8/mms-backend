<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class ComplaintEndpoints
{
    #[OA\Post(
        path: '/complaints/guest',
        operationId: 'submitComplaintGuest',
        tags: ['Complaints'],
        summary: 'Submit a complaint as a guest',
        description: 'Allows non-authenticated users or unverified users to submit complaints.',        requestBody: new OA\RequestBody(
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
                        new OA\Property(property: 'files', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
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
                        new OA\Property(property: 'message', type: 'string', example: 'تم تقديم الشكوى بنجاح. يمكنك تتبعها باستخدام رقم الشكوى الخاص بك.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'complaint_number', type: 'string', example: 'CMP-2026-ABC123'),
                                new OA\Property(property: 'title', type: 'string', example: 'Broken AC in prayer hall'),
                                new OA\Property(property: 'description', type: 'string', example: 'The air conditioning has been out of service for two days.'),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'priority', type: 'string', example: 'high'),
                                new OA\Property(property: 'complaint_type', type: 'string', example: 'power_outage'),
                                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
                                new OA\Property(property: 'is_anonymous', type: 'boolean', example: false),
                                new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-13T08:30:00Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-13T08:30:00Z'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
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
                        new OA\Property(property: 'files', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
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
                        new OA\Property(property: 'message', type: 'string', example: 'تم تقديم الشكوى بنجاح. يمكنك تتبعها باستخدام رقم الشكوى الخاص بك.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'complaint_number', type: 'string', example: 'CMP-2026-ABC123'),
                                new OA\Property(property: 'title', type: 'string', example: 'Broken AC in prayer hall'),
                                new OA\Property(property: 'description', type: 'string', example: 'The air conditioning has been out of service for two days.'),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'priority', type: 'string', example: 'high'),
                                new OA\Property(property: 'complaint_type', type: 'string', example: 'power_outage'),
                                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
                                new OA\Property(property: 'is_anonymous', type: 'boolean', example: false),
                                new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-13T08:30:00Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-13T08:30:00Z'),
                            ]
                        ),
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
                                    new OA\Property(property: 'id', type: 'integer', example: 12),
                                    new OA\Property(property: 'complaint_number', type: 'string', example: 'CMP-2026-ABC123'),
                                    new OA\Property(property: 'title', type: 'string', example: 'Broken AC in prayer hall'),
                                    new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                    new OA\Property(property: 'priority', type: 'string', example: 'high'),
                                    new OA\Property(property: 'complaint_type', type: 'string', example: 'power_outage'),
                                    new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-13T08:30:00Z'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
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
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 12),
                                new OA\Property(property: 'complaint_number', type: 'string', example: 'CMP-2026-ABC123'),
                                new OA\Property(property: 'title', type: 'string', example: 'Broken AC in prayer hall'),
                                new OA\Property(property: 'description', type: 'string', example: 'The air conditioning has been out of service for two days.'),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'priority', type: 'string', example: 'high'),
                                new OA\Property(property: 'complaint_type', type: 'string', example: 'power_outage'),
                                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
                                new OA\Property(property: 'is_anonymous', type: 'boolean', example: false),
                                new OA\Property(property: 'admin_notes', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-13T08:30:00Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-13T09:30:00Z'),
                            ]
                        ),
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
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 12),
                                new OA\Property(property: 'complaint_number', type: 'string', example: 'CMP-2026-ABC123'),
                                new OA\Property(property: 'status', type: 'string', example: 'resolved'),
                                new OA\Property(property: 'admin_notes', type: 'string', nullable: true, example: 'Resolved after maintenance team fixed the issue.'),
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
                        new OA\Property(property: 'message', type: 'string', example: 'تم استرجاع الإحصائيات بنجاح'),
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

}
