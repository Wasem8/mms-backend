<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class MosqueEndpoints
{


    #[OA\Get(
        path: '/mosques',
        operationId: 'getMosques',
        tags: ['Mosques'],
        summary: 'List all mosques',
        description: 'Returns a paginated list of all mosques.',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mosques retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosques retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Al-Rahma Mosque'),
                                    new OA\Property(property: 'image', type: 'string', nullable: true, example: '/storage/mosques/mosque.jpg'),
                                    new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '5:00 AM - 10:00 PM'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                                    new OA\Property(property: 'is_featured', type: 'boolean', example: 0),                                    new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Cairo'),
                                    new OA\Property(property: 'district', type: 'string', nullable: true, example: 'Downtown'),
                                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5),
                                    new OA\Property(property: 'reviews_count', type: 'integer', example: 10),
                                    new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Ahmed'),
                                    new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Mohamed'),
                                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 2),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 50),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 4),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/mosques/search',
        operationId: 'searchMosques',
        tags: ['Mosques'],
        summary: 'Search mosques by  name, city, or district',
        description: 'Search mosques by name, city, or district.',
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string', example: 'Al-Rahma')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search results retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Search results retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Al-Rahma Mosque'),
                                    new OA\Property(property: 'image', type: 'string', nullable: true, example: '/storage/mosques/mosque.jpg'),
                                    new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '5:00 AM - 10:00 PM'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                                    new OA\Property(property: 'is_featured', type: 'boolean', example: 0),                                    new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Cairo'),
                                    new OA\Property(property: 'district', type: 'string', nullable: true, example: 'Downtown'),
                                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5),
                                    new OA\Property(property: 'reviews_count', type: 'integer', example: 10),
                                    new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Ahmed'),
                                    new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Mohamed'),
                                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 2),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 1),
                            ]
                        ),
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
                        new OA\Property(property: 'data', type: 'object', example: ['q' => ['The q field is required.']]),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function search() {}

    #[OA\Get(
        path: '/mosques/featured',
        operationId: 'getFeaturedMosques',
        tags: ['Mosques'],
        summary: 'List featured mosques',
        description: 'Returns a list of mosques marked as featured.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Featured mosques retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Featured mosques retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 2),
                                    new OA\Property(property: 'name', type: 'string', example: 'Al-Nour Mosque'),
                                    new OA\Property(property: 'image', type: 'string', nullable: true, example: '/storage/mosques/nour.jpg'),
                                    new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '5:00 AM - 10:00 PM'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                                    new OA\Property(property: 'is_featured', type: 'boolean', example: true),
                                    new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Alexandria'),
                                    new OA\Property(property: 'district', type: 'string', nullable: true, example: 'Corniche'),
                                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 31.2001),
                                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 29.9187),
                                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.8),
                                    new OA\Property(property: 'reviews_count', type: 'integer', example: 15),
                                    new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Hassan'),
                                    new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Ali'),
                                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 3),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
        ]
    )]
    public function featured() {}

    #[OA\Get(
        path: '/mosques/city/{city}',
        operationId: 'getMosquesByCity',
        tags: ['Mosques'],
        summary: 'List mosques by city',
        description: 'Returns all mosques located in the specified city.',
        parameters: [
            new OA\Parameter(name: 'city', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'Cairo')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mosques retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosques retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Al-Rahma Mosque'),
                                    new OA\Property(property: 'image', type: 'string', nullable: true, example: '/storage/mosques/rahma.jpg'),
                                    new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '5:00 AM - 10:00 PM'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                                    new OA\Property(property: 'is_featured', type: 'boolean', example: 0),                                    new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Cairo'),
                                    new OA\Property(property: 'district', type: 'string', nullable: true, example: 'Downtown'),
                                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.2),
                                    new OA\Property(property: 'reviews_count', type: 'integer', example: 8),
                                    new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Ahmed'),
                                    new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Mohamed'),
                                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 2),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No mosques found for this city',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'No mosques found.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function byCity() {}

    #[OA\Get(
        path: '/mosques/{id}',
        operationId: 'getMosque',
        tags: ['Mosques'],
        summary: 'Get a single mosque',
        description: 'Returns the details of a specific mosque by its ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mosque retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Al-Rahma Mosque'),
                                new OA\Property(property: 'image', type: 'string', nullable: true, example: '/storage/mosques/rahma.jpg'),
                                new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '5:00 AM - 10:00 PM'),
                                new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                                new OA\Property(property: 'is_featured', type: 'boolean', example: 0),                                new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Cairo'),
                                new OA\Property(property: 'district', type: 'string', nullable: true, example: 'Downtown'),
                                new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                                new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                                new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5),
                                new OA\Property(property: 'reviews_count', type: 'integer', example: 10),
                                new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Ahmed'),
                                new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Mohamed'),
                                new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 2),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Mosque not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque not found.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function show() {}

    #[OA\Post(
        path: '/mosques',
        operationId: 'createMosque',
        tags: ['Mosques'],
        summary: 'Create a new mosque',
        description: 'Creates a new mosque record. Requires authentication.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Al-Rahma Mosque', minLength: 2, maxLength: 255),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Mosque image file to upload'),
                        new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '5:00 AM - 10:00 PM'),
                        new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                        new OA\Property(property: 'is_featured', type: 'boolean', example: 0),                        new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Cairo'),
                        new OA\Property(property: 'district', type: 'string', nullable: true, example: 'Downtown'),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                        new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Ahmed'),
                        new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Mohamed'),
                        new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 2, description: 'User ID of a mosque_manager role user.'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Mosque created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque created successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 10),
                                new OA\Property(property: 'name', type: 'string', example: 'Al-Rahma Mosque'),
                                new OA\Property(property: 'image', type: 'string', nullable: true, example: '/storage/mosques/mosque.jpg'),
                                new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '5:00 AM - 10:00 PM'),
                                new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                                new OA\Property(property: 'is_featured', type: 'boolean', example: 0),                                new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Cairo'),
                                new OA\Property(property: 'district', type: 'string', nullable: true, example: 'Downtown'),
                                new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                                new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                                new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 0.0),
                                new OA\Property(property: 'reviews_count', type: 'integer', example: 0),
                                new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Ahmed'),
                                new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Mohamed'),
                                new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 2),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
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
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
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
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function store() {}

    #[OA\Put(
        path: '/mosques/{mosque}',
        operationId: 'updateMosque',
        tags: ['Mosques'],
        summary: 'Update a mosque',
        description: 'Updates an existing mosque record. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: '_method', type: 'string', example: 'PUT', description: 'Included if sending via POST to handle multipart data in some frameworks'),
                        new OA\Property(property: 'name', type: 'string', example: 'Al-Rahma Mosque Updated', minLength: 2, maxLength: 255),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'New mosque image file to upload'),
                        new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '6:00 AM - 11:00 PM'),
                        new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                        new OA\Property(property: 'is_featured', type: 'boolean', example: true),
                        new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Cairo'),
                        new OA\Property(property: 'district', type: 'string', nullable: true, example: 'New District'),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                        new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Ahmed Updated'),
                        new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Mohamed Updated'),
                        new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 3, description: 'User ID of a mosque_manager role user.'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mosque updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque updated successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Al-Rahma Mosque Updated'),
                                new OA\Property(property: 'image', type: 'string', nullable: true, example: '/storage/mosques/updated.jpg'),
                                new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '6:00 AM - 11:00 PM'),
                                new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'active'),
                                new OA\Property(property: 'is_featured', type: 'boolean', example: true),
                                new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Cairo'),
                                new OA\Property(property: 'district', type: 'string', nullable: true, example: 'New District'),
                                new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                                new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                                new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5),
                                new OA\Property(property: 'reviews_count', type: 'integer', example: 10),
                                new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'Sheikh Ahmed Updated'),
                                new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'Sheikh Mohamed Updated'),
                                new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 3),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mosque not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/mosques/{mosque}',
        operationId: 'deleteMosque',
        tags: ['Mosques'],
        summary: 'Delete a mosque',
        description: 'Permanently deletes a mosque record. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mosque deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque deleted successfully.'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mosque not found'),
        ]
    )]
    public function destroy() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/status',
        operationId: 'updateMosqueStatus',
        tags: ['Mosques'],
        summary: 'Update mosque status',
        description: 'Updates only the status of a mosque (active/inactive). Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'maintenance', 'closed'], example: 'maintenance'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque status updated successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string', example: 'maintenance'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mosque not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateStatus() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/featured',
        operationId: 'toggleMosqueFeatured',
        tags: ['Mosques'],
        summary: 'Toggle mosque featured flag',
        description: 'Toggles the featured state of a mosque. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Featured status toggled successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque featured status updated.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'is_featured', type: 'boolean', example: true),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mosque not found'),
        ]
    )]
    public function toggleFeatured() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/rating',
        operationId: 'updateMosqueRating',
        tags: ['Mosques'],
        summary: 'Update mosque rating',
        description: 'Updates the rating of a mosque. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['average_rating'],
                properties: [
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', minimum: 0, maximum: 5, example: 4.5),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Rating updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque rating updated successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mosque not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateRating() {}

    // ─────────────────────────────────────────────
    //  FACILITIES ENDPOINTS
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/facilities',
        operationId: 'getFacilities',
        tags: ['Facilities'],
        summary: 'List all facilities /{public endpoint}',
        description: 'Returns a list of all available facilities. Public endpoint.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Facilities retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Facilities retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Parking'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
        ]
    )]
    public function facilitiesIndex() {}

    #[OA\Post(
        path: '/facilities',
        operationId: 'createFacility',
        tags: ['Facilities'],
        summary: 'Create a new facility / {Region Manager Only}',
        description: 'Creates a new standalone facility. Requires authentication.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Parking', minLength: 2, maxLength: 255),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Facility created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Facility created successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 5),
                                new OA\Property(property: 'name', type: 'string', example: 'Parking'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(property: 'data', type: 'object', example: ['field_name' => ['Error message']]),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function facilitiesStore() {}

    #[OA\Put(
        path: '/facilities/{facility}',
        operationId: 'updateFacility',
        tags: ['Facilities'],
        summary: 'Update a facility / {Region Manager Only}',
        description: 'Updates an existing facility. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'facility', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 5)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Parking Updated', minLength: 2, maxLength: 255),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Facility updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Facility updated successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 5),
                                new OA\Property(property: 'name', type: 'string', example: 'Parking Updated'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Facility not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateFacility() {}

    #[OA\Delete(
        path: '/facilities/{facility}',
        operationId: 'deleteFacility',
        tags: ['Facilities'],
        summary: 'Delete a facility /{Region manager Only}',
        description: 'Permanently deletes a facility. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'facility', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 5)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Facility deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Facility deleted successfully.'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Facility not found'),
        ]
    )]
    public function destroyFacility() {}

    #[OA\Get(
        path: '/mosques/{mosque}/facilities',
        operationId: 'getMosqueFacilities',
        tags: ['Facilities'],
        summary: 'List facilities for a mosque /{public endpoint}',
        description: 'Returns all facilities associated with a specific mosque. Public endpoint.',
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Facilities retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Facilities retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Parking'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Mosque not found'),
        ]
    )]
    public function byMosque() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/attach',
        operationId: 'attachMosqueFacilities',
        tags: ['Facilities'],
        summary: 'Attach facilities to a mosque (add facilities to exists facilities) / {Mosque Manager Only}',
        description: 'Adds one or more facilities to a mosque without removing existing ones. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['facility_ids'],
                properties: [
                    new OA\Property(
                        property: 'facility_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [4, 5]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Facilities attached successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Facilities attached successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 4),
                                    new OA\Property(property: 'name', type: 'string', example: 'Prayer Mats'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mosque not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function attach() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/detach',
        operationId: 'detachMosqueFacilities',
        tags: ['Facilities'],
        summary: 'Detach facilities from a mosque (delete this facilities from mosque) / {Mosque Manager Only}',
        description: 'Removes one or more facilities from a mosque. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['facility_ids'],
                properties: [
                    new OA\Property(
                        property: 'facility_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [2, 3]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Facilities detached successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Facilities detached successfully.'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mosque not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function detach() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/sync',
        operationId: 'syncMosqueFacilities',
        tags: ['Facilities'],
        summary: 'Sync facilities for a mosque (delete exists facilities and add array of facilities )/ {Mosque Manager Only}',
        description: 'Replaces all existing facility associations with the provided list. Requires authentication.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['facility_ids'],
                properties: [
                    new OA\Property(
                        property: 'facility_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2, 3]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Facilities synced successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Facilities synced successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Parking'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Mosque not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function sync() {}
}
