<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class DonationsEndpoints
{
    #[OA\Get(
        path: '/donations',
        operationId: 'listDonations',
        tags: ['Donations'],
        summary: 'List donations',
        description: 'Retrieve a list of donations. This endpoint is public.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Donations retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Donations retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'reference', type: 'string', example: 'DON-ABCD1234'),
                                    new OA\Property(property: 'mosque_id', type: 'integer', example: 5, nullable: true),
                                    new OA\Property(property: 'mosque_need_id', type: 'integer', example: 3, nullable: true),
                                    new OA\Property(property: 'campaign_id', type: 'integer', example: 2, nullable: true),
                                    new OA\Property(property: 'user_id', type: 'integer', example: 7, nullable: true),
                                    new OA\Property(property: 'type', type: 'string', example: 'cash'),
                                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 150.00, nullable: true),
                                    new OA\Property(property: 'item_description', type: 'string', example: 'Winter blankets', nullable: true),
                                    new OA\Property(property: 'donor_name', type: 'string', example: 'Ahmed Ali', nullable: true),
                                    new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                    new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', example: '2026-05-12T12:00:00Z', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-12T11:00:00Z'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-12T11:10:00Z'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true)
                    ]
                )
            )
        ]
    )]
    public function listDonations() {}

    #[OA\Get(
        path: '/donations/{id}',
        operationId: 'getDonation',
        tags: ['Donations'],
        summary: 'Get donation details',
        description: 'Retrieve a single donation by ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Donation retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Donation retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'reference', type: 'string', example: 'DON-ABCD1234'),
                                new OA\Property(property: 'mosque_id', type: 'integer', example: 5, nullable: true),
                                new OA\Property(property: 'mosque_need_id', type: 'integer', example: 3, nullable: true),
                                new OA\Property(property: 'campaign_id', type: 'integer', example: 2, nullable: true),
                                new OA\Property(property: 'user_id', type: 'integer', example: 7, nullable: true),
                                new OA\Property(property: 'type', type: 'string', example: 'cash'),
                                new OA\Property(property: 'amount', type: 'number', format: 'float', example: 150.00, nullable: true),
                                new OA\Property(property: 'item_description', type: 'string', example: 'Winter blankets', nullable: true),
                                new OA\Property(property: 'donor_name', type: 'string', example: 'Ahmed Ali', nullable: true),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', example: '2026-05-12T12:00:00Z', nullable: true),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-12T11:00:00Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-12T11:10:00Z'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Donation not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Donation not found'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            )
        ]
    )]
    public function getDonation() {}

    #[OA\Post(
        path: '/donations',
        operationId: 'createDonation',
        tags: ['Donations'],
        summary: 'Create a donation',
        description: 'Submit a new donation.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mosque_id', 'type'],
                properties: [
                    new OA\Property(property: 'mosque_id', type: 'integer', example: 5),
                    new OA\Property(property: 'mosque_need_id', type: 'integer', nullable: true, example: 3),
                    new OA\Property(property: 'campaign_id', type: 'integer', nullable: true, example: 2),

                    new OA\Property(property: 'type', type: 'string', enum: ['cash', 'kind'], example: 'cash'),

                    new OA\Property(property: 'amount', type: 'number', format: 'float', nullable: true, example: 150.00),

                    new OA\Property(property: 'item_description', type: 'string', nullable: true, example: 'Winter blankets'),
                    new OA\Property(property: 'donor_name', type: 'string', nullable: true, example: 'Ahmed Ali'),

                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'completed'], example: 'pending'),

                    new OA\Property(property: 'reference', type: 'string', nullable: true, example: 'DON-ABCD1234'),

                    new OA\Property(property: 'user_id', type: 'integer', nullable: true, example: 7),

                    new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', nullable: true)
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 201,
                description: 'Donation created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Donation created successfully'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(property: 'data', type: 'object', example: ['field_name' => ['Error message']]),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            )
        ]
    )]
    public function createDonation() {}

    #[OA\Put(
        path: '/donations/{id}',
        operationId: 'updateDonation',
        tags: ['Donations'],
        summary: 'Update a donation',
        description: 'Update donation details. Authentication is required.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'mosque_id', type: 'integer', example: 5, nullable: true),
                    new OA\Property(property: 'mosque_need_id', type: 'integer', example: 3, nullable: true),
                    new OA\Property(property: 'campaign_id', type: 'integer', example: 2, nullable: true),
                    new OA\Property(property: 'type', type: 'string', example: 'cash', enum: ['cash', 'kind'], nullable: true),
                    new OA\Property(property: 'item_description', type: 'string', example: 'Winter blankets', nullable: true),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 200.00, nullable: true),
                    new OA\Property(property: 'donor_name', type: 'string', example: 'Ahmed Ali', nullable: true),
                    new OA\Property(property: 'status', type: 'string', example: 'completed', enum: ['pending', 'completed'], nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Donation updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Donation updated successfully'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            )
        ]
    )]
    public function updateDonation() {}

    #[OA\Delete(
        path: '/donations/{id}',
        operationId: 'deleteDonation',
        tags: ['Donations'],
        summary: 'Delete a donation',
        description: 'Delete a donation by ID. Authentication is required.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Donation deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Donation deleted successfully'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            )
        ]
    )]
    public function deleteDonation() {}
}
