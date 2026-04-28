<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * MMS API Documentation - OpenAPI/Swagger Configuration
 */
#[OA\Info(
    title: 'MMS API Documentation',
    version: "1.0.0",
    description: 'API documentation for Mosque Management System (MMS)',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'support@example.com',
        url: 'https://mms-support.example.com'
    ),
    license: new OA\License(
        name: 'MIT',
        identifier: 'MIT'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Local Development Server'
)]
#[OA\Server(
    url: 'https://api.mms-system.com',
    description: 'Production API Server'
)]

// ========== SECURITY SCHEMES ==========
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Enter your Bearer token'
)]

// ========== GLOBAL COMPONENTS (Responses & Schemas) ==========
#[OA\Components(
    responses: [
        // استجابة 401: غير مسجل دخول
        'Unauthenticated' => new OA\Response(
            response: 'Unauthenticated',
            description: 'Unauthenticated - Missing or invalid token',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                    new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                ]
            )
        ),

        // استجابة 403: صلاحيات غير كافية (التي طلبتها)
        'Forbidden' => new OA\Response(
            response: 'Forbidden',
            description: 'Forbidden - Insufficient permissions',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Access denied. Your role does not allow this action.'),
                    new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                    new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                ]
            )
        ),

        // استجابة 404: مورد غير موجود
        'NotFound' => new OA\Response(
            response: 'NotFound',
            description: 'Resource not found',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
                    new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                    new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                ]
            )
        ),

        // استجابة 422: خطأ في التحقق من البيانات
        'ValidationError' => new OA\Response(
            response: 'ValidationError',
            description: 'Validation failed',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                    new OA\Property(property: 'data', type: 'object', example: ["field" => ["Error details"]]),
                    new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                ]
            )
        )
    ],
    schemas: [
        // تعريف الـ Pagination لكي يعمل ref: '#/components/schemas/Pagination'
        'Pagination' => new OA\Schema(
            schema: 'Pagination',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                new OA\Property(property: 'total', type: 'integer', example: 50),
                new OA\Property(property: 'has_more_pages', type: 'boolean', example: true),
            ]
        )
    ]
)]

class OpenApiSpec
{
}

class MosqueSpec
{
    // ==========================================
    // PUBLIC MOSQUE ENDPOINTS
    // ==========================================

    #[OA\Get(
        path: '/mosques',
        operationId: 'getMosques',
        tags: ['Mosques'],
        summary: 'Get a list of all mosques (Public)',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation')
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/mosques/featured',
        operationId: 'getFeaturedMosques',
        tags: ['Mosques'],
        summary: 'Get featured mosques (Public)',
        responses: [
            new OA\Response(response: 200, description: 'Featured mosques retrieved successfully')
        ]
    )]
    public function featured() {}

    #[OA\Get(
        path: '/mosques/city/{city}',
        operationId: 'getMosquesByCity',
        tags: ['Mosques'],
        summary: 'Get mosques filtered by city (Public)',
        parameters: [
            new OA\Parameter(name: 'city', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mosques retrieved successfully')
        ]
    )]
    public function byCity() {}

    #[OA\Get(
        path: '/mosques/{id}',
        operationId: 'showMosque',
        tags: ['Mosques'],
        summary: 'Get details of a specific mosque (Public)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mosque details retrieved successfully'),
            new OA\Response(response: 404, description: 'Mosque not found')
        ]
    )]
    public function show() {}

    // ==========================================
    // PROTECTED MOSQUE ENDPOINTS (Auth Required)
    // ==========================================

    /**
     * إنشاء مسجد جديد
     */
    #[OA\Post(
        path: '/mosques',
        operationId: 'storeMosque',
        tags: ['Mosques'],
        summary: 'Create a new mosque',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'city', 'district', 'status'], // يمكنك تعديل الحقول الإجبارية حسب الـ Request الخاص بك
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'مسجد النور'),
                    new OA\Property(property: 'cover_image', type: 'string', nullable: true, example: 'https://example.com/mosque.jpg'),
                    new OA\Property(property: 'working_hours', type: 'string', nullable: true, example: '04:00 AM - 10:00 PM'),
                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                    new OA\Property(property: 'is_featured', type: 'boolean', example: false),
                    new OA\Property(property: 'city', type: 'string', example: 'Riyadh'),
                    new OA\Property(property: 'district', type: 'string', example: 'Al-Malqa'),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 24.7135517),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 46.6752957),
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5),
                    new OA\Property(property: 'reviews_count', type: 'integer', example: 120),
                    new OA\Property(property: 'imam', type: 'string', nullable: true, example: 'الشيخ عبدالله'),
                    new OA\Property(property: 'khatib', type: 'string', nullable: true, example: 'الشيخ محمد'),
                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true, example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Mosque created successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function store() {}

    /**
     * تحديث بيانات المسجد
     */
    #[OA\Put(
        path: '/mosques/{mosque}',
        operationId: 'updateMosque',
        tags: ['Mosques'],
        summary: 'Update an existing mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'مسجد النور (محدث)'),
                    new OA\Property(property: 'cover_image', type: 'string', nullable: true),
                    new OA\Property(property: 'working_hours', type: 'string', nullable: true),
                    new OA\Property(property: 'status', type: 'string', example: 'maintenance'),
                    new OA\Property(property: 'is_featured', type: 'boolean'),
                    new OA\Property(property: 'city', type: 'string'),
                    new OA\Property(property: 'district', type: 'string'),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float'),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float'),
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float'),
                    new OA\Property(property: 'reviews_count', type: 'integer'),
                    new OA\Property(property: 'imam', type: 'string', nullable: true),
                    new OA\Property(property: 'khatib', type: 'string', nullable: true),
                    new OA\Property(property: 'manager_id', type: 'integer', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mosque updated successfully')
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/mosques/{mosque}',
        operationId: 'destroyMosque',
        tags: ['Mosques'],
        summary: 'Delete a mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mosque deleted successfully')
        ]
    )]
    public function destroy() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/status',
        operationId: 'updateMosqueStatus',
        tags: ['Mosques'],
        summary: 'Update mosque status',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'maintenance')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Status updated successfully')
        ]
    )]
    public function updateStatus() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/featured',
        operationId: 'toggleMosqueFeatured',
        tags: ['Mosques'],
        summary: 'Toggle if the mosque is featured',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Featured status toggled successfully')
        ]
    )]
    public function toggleFeatured() {}

    #[OA\Patch(
        path: '/mosques/{mosque}/rating',
        operationId: 'updateMosqueRating',
        tags: ['Mosques'],
        summary: 'Update the average rating and review count of the mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.8),
                    new OA\Property(property: 'reviews_count', type: 'integer', example: 121)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Rating updated successfully')
        ]
    )]
    public function updateRating() {}


    // ==========================================
    // FACILITIES ENDPOINTS
    // ==========================================

    #[OA\Get(
        path: '/{mosque}/facilities',
        operationId: 'getPublicFacilities',
        tags: ['Facilities'],
        summary: 'Get all facilities for a mosque (Public)',
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Facilities retrieved successfully')
        ]
    )]
    public function indexFacilities() {}

    #[OA\Get(
        path: '/mosques/{mosque}/facilities',
        operationId: 'getFacilitiesByMosque',
        tags: ['Facilities'],
        summary: 'Get attached facilities for a mosque (Auth required)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Attached facilities retrieved successfully')
        ]
    )]
    public function byMosqueFacilities() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/sync',
        operationId: 'syncFacilities',
        tags: ['Facilities'],
        summary: 'Sync facilities for a mosque (replaces old ones)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'facility_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Facilities synced successfully')
        ]
    )]
    public function syncFacilities() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/attach',
        operationId: 'attachFacility',
        tags: ['Facilities'],
        summary: 'Attach a specific facility to a mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'facility_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Facility attached successfully')
        ]
    )]
    public function attachFacility() {}

    #[OA\Post(
        path: '/mosques/{mosque}/facilities/detach',
        operationId: 'detachFacility',
        tags: ['Facilities'],
        summary: 'Detach a specific facility from a mosque',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mosque', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'facility_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Facility detached successfully')
        ]
    )]
    public function detachFacility() {}
}
