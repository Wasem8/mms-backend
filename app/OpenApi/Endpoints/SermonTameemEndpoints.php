<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Sermons',
    description: 'Manage mosque sermons (خطب). Covers upload, approval, and rejection workflows.'
)]
#[OA\Tag(
    name: 'Tameems',
    description: 'Manage circulars/announcements (تعاميم) sent between mosque managers.'
)]
#[OA\Tag(
    name: 'Sermon Selections',
    description: 'Manage sermon selections for Friday prayers (اختيار خطبة الجمعة).'
)]
class SermonTameemEndpoints
{

    #[OA\Schema(
        schema: 'Sermon',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',          type: 'integer', example: 1),
            new OA\Property(property: 'title',        type: 'string',  example: 'خطبة الجمعة - التوبة والإنابة'),
            new OA\Property(property: 'content',      type: 'string',  example: 'الحمد لله رب العالمين...'),
            new OA\Property(
                property: 'category',
                type: 'string',
                enum: ['creed_faith', 'jurisprudence_rulings', 'ethics_conduct', 'contemporary_issues', 'occasions_seasons','other'],
                nullable: true,
                example: 'occasions_seasons'
            ),
            new OA\Property(
                property: 'status',
                type: 'string',
                enum: ['pending', 'Scheduled', 'rejected','completed'],
                example: 'pending'
            ),
            new OA\Property(property: 'notes',              type: 'string',  nullable: true, example: 'يرجى مراجعة المقدمة'),
            new OA\Property(property: 'mosque_manager_id',  type: 'integer', example: 3),
            new OA\Property(property: 'region_manager_id',  type: 'integer', nullable: true, example: 7),
            new OA\Property(
                property: 'attachments',
                type: 'array',
                items: new OA\Items(type: 'string', example: 'https://storage.example.com/sermons/file.pdf'),
                nullable: true
            ),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ]
    )]
    public function schemaSermon() {}

    #[OA\Schema(
        schema: 'Tameem',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',        type: 'integer', example: 10),
            new OA\Property(property: 'title',      type: 'string',  example: 'تعميم بشأن مواعيد الصلاة'),
            new OA\Property(property: 'content',    type: 'string',  example: 'يُعلم جميع أئمة المساجد...'),
            new OA\Property(property: 'sender_id',  type: 'integer', example: 2),
            new OA\Property(
                property: 'recipients',
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer', example: 5),
                        new OA\Property(property: 'name',      type: 'string',  example: 'أحمد محمد'),
                        new OA\Property(property: 'is_read',   type: 'boolean', example: false),
                        new OA\Property(property: 'read_at',   type: 'string',  format: 'date-time', nullable: true),
                    ]
                )
            ),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ]
    )]
    public function schemaTameem() {}

    #[OA\Schema(
        schema: 'SermonSelection',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'sermon_id', type: 'integer', example: 5),
            new OA\Property(property: 'mosque_manager_id', type: 'integer', example: 3),
            new OA\Property(property: 'friday_date', type: 'string', format: 'date', example: '2026-07-18'),
            new OA\Property(property: 'sermon', ref: '#/components/schemas/Sermon'),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ]
    )]
    public function schemaSermonSelection() {}



    // =========================================================================
    // GET /sermons
    // =========================================================================
    #[OA\Post(
        path: '/sermons',
        operationId: 'storeSermon',
        tags: ['Sermons'],
        summary: 'Submit a sermon',
        description: 'Allows mosque managers to submit a sermon for approval. Multiple attachments are supported. Allowed file types: PDF, DOC, DOCX, JPG, JPEG and PNG. Maximum size: 5MB per file.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'content', 'speaker_name', 'category', 'sermon_date'],
                    properties: [
                        new OA\Property(
                            property: 'title',
                            type: 'string',
                            example: 'خطبة الجمعة - التوبة والإنابة'
                        ),

                        new OA\Property(
                            property: 'content',
                            type: 'string',
                            example: 'الحمد لله رب العالمين...'
                        ),

                        new OA\Property(
                            property: 'speaker_name',
                            type: 'string',
                            example: 'الشيخ أحمد'
                        ),

                        new OA\Property(
                            property: 'category',
                            type: 'string',
                            enum: ['creed_faith', 'jurisprudence_rulings', 'ethics_conduct', 'contemporary_issues', 'occasions_seasons', 'other'],
                            example: 'occasions_seasons',
                            description: 'تصنيف الخطبة'
                        ),

                        new OA\Property(
                            property: 'sermon_date',
                            type: 'string',
                            format: 'date',
                            example: '2026-07-15'
                        ),

                        new OA\Property(
                            property: 'attachments[]',
                            description: 'Optional sermon attachments. Allowed: PDF, DOC, DOCX, JPG, JPEG and PNG. Max 5MB per file.',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                format: 'binary'
                            )
                        ),
                    ]
                )
            )
        ),

        responses: [
            new OA\Response(
                response: 201,
                description: 'Sermon submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم تقديم الخطبة بنجاح'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Sermon'
                        ),
                    ],
                    example: [
                        'message' => 'تم تقديم الخطبة بنجاح',
                        'data' => [
                            'id'                 => 1,
                            'title'              => 'خطبة الجمعة - التوبة والإنابة',
                            'content'            => 'الحمد لله رب العالمين...',
                            'category'           => 'occasions_seasons',
                            'status'             => 'pending',
                            'notes'              => null,
                            'mosque_manager_id'  => 3,
                            'region_manager_id'  => null,
                            'attachments'        => ['https://storage.example.com/sermons/file.pdf'],
                            'created_at'         => '2026-08-15 09:05:23',
                            'updated_at'         => '2026-08-15 09:05:23',
                        ],
                    ],
                )
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                description: 'Only mosque managers can submit sermons'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'خطأ في التحقق من البيانات'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function storeSermon() {}

    #[OA\Get(
        path: '/sermons/most-selected',
        operationId: 'mostSelectedSermons',
        tags: ['Sermons'],
        summary: 'Get most selected sermons',
        description: 'Returns archived sermons ranked by how many times they were selected for a Friday sermon, optionally within a date range.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 10)),
            new OA\Parameter(name: 'friday_date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'friday_date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب الخطب الأكثر اختيارًا بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                allOf: [
                                    new OA\Schema(ref: '#/components/schemas/Sermon'),
                                    new OA\Schema(properties: [
                                        new OA\Property(property: 'selections_count', type: 'integer', example: 12),
                                    ]),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function mostSelectedSermons() {}

    #[OA\Get(
        path: '/sermons/{id}',
        operationId: 'getSermonById',
        tags: ['Sermons'],
        summary: 'Get sermon by ID',
        description: 'Returns the details of a specific sermon.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Sermon ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sermon retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم جلب الخطبة بنجاح'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Sermon'
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 404,
                description: 'Sermon not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'الخطبة غير موجودة'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getSermonById() {}
    #[OA\Get(
        path: '/sermons/pending',
        operationId: 'pendingSermons',
        tags: ['Sermons'],
        summary: 'Get pending sermons',
        description: 'Returns a paginated list of sermons awaiting approval.',
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
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم جلب الخطب المعلقة بنجاح'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Sermon')
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page',    type: 'integer', example: 1),
                                new OA\Property(property: 'last_page',       type: 'integer', example: 3),
                                new OA\Property(property: 'per_page',        type: 'integer', example: 15),
                                new OA\Property(property: 'total',           type: 'integer', example: 32),
                                new OA\Property(property: 'has_more_pages',  type: 'boolean', example: true),
                            ]
                        ),
                    ],
                    example: [
                        'status'  => true,
                        'message' => 'تم جلب الخطب المعلقة بنجاح',
                        'data' => [
                            [
                                'id'                 => 1,
                                'title'              => 'خطبة الجمعة - التوبة والإنابة',
                                'content'            => 'الحمد لله رب العالمين...',
                                'category'           => 'occasions_seasons',
                                'status'             => 'pending',
                                'notes'              => 'يرجى مراجعة المقدمة',
                                'mosque_manager_id'  => 3,
                                'region_manager_id'  => null,
                                'attachments'        => ['https://storage.example.com/sermons/file.pdf'],
                                'created_at'         => '2026-08-15 09:05:23',
                                'updated_at'         => '2026-08-15 09:05:23',
                            ],
                        ],
                        'pagination' => [
                            'current_page'   => 1,
                            'last_page'      => 3,
                            'per_page'       => 15,
                            'total'          => 32,
                            'has_more_pages' => true,
                        ],
                    ],
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function pendingSermons() {}

    #[OA\Get(
        path: '/sermons/archived',
        operationId: 'archivedSermons',
        tags: ['Sermons'],
        summary: 'Get archived sermons',
        description: 'Returns a paginated list of approved, rejected, or completed sermons.',
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
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم جلب أرشيف الخطب بنجاح'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Sermon')
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page',    type: 'integer', example: 1),
                                new OA\Property(property: 'last_page',       type: 'integer', example: 5),
                                new OA\Property(property: 'per_page',        type: 'integer', example: 15),
                                new OA\Property(property: 'total',           type: 'integer', example: 67),
                                new OA\Property(property: 'has_more_pages',  type: 'boolean', example: true),
                            ]
                        ),
                    ],
                    example: [
                        'status'  => true,
                        'message' => 'تم جلب أرشيف الخطب بنجاح',
                        'data' => [
                            [
                                'id'                 => 12,
                                'title'              => 'خطبة الجمعة - بر الوالدين',
                                'content'            => 'الحمد لله رب العالمين والصلاة والسلام على رسول الله...',
                                'category'           => 'ethics_conduct',
                                'status'             => 'Archived',
                                'notes'              => null,
                                'mosque_manager_id'  => 3,
                                'region_manager_id'  => 7,
                                'attachments'        => ['https://storage.example.com/sermons/archive.pdf'],
                                'created_at'         => '2026-07-20 12:30:00',
                                'updated_at'         => '2026-07-21 08:00:00',
                            ],
                        ],
                        'pagination' => [
                            'current_page'   => 1,
                            'last_page'      => 5,
                            'per_page'       => 15,
                            'total'          => 67,
                            'has_more_pages' => true,
                        ],
                    ],
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function archivedSermons() {}


    #[OA\Get(
        path: '/sermons',
        operationId: 'indexSermons',
        tags: ['Sermons'],
        summary: 'Get all sermons',
        description: 'Returns a list of all sermons.',
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
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم جلب الخطب بنجاح'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Sermon')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function indexSermons() {}

    // =========================================================================
    // DELETE /sermons/{id}
    // =========================================================================
    #[OA\Delete(
        path: '/sermons/{id}',
        operationId: 'deleteSermon',
        tags: ['Sermons'],
        summary: 'Delete a pending sermon',
        description: <<<DESC
        Allows a mosque manager to permanently delete their own sermon submission,
        but only while it is still `Pending`. Once a sermon has been approved or
        rejected by a region manager, it can no longer be deleted through this endpoint.
        - Only the mosque manager who submitted the sermon may delete it.
        - Returns `403` if the authenticated user is not the original submitter.
        - Returns `409` if the sermon is no longer `Pending`.
        DESC,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Sermon ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sermon deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم حذف الخطبة بنجاح'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(
                response: 403,
                description: 'Forbidden — not the original submitter',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'غير مصرح لك بحذف هذه الخطبة'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Sermon not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'الخطبة غير موجودة'),
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflict — sermon is no longer pending',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'لا يمكن حذف خطبة تمت مراجعتها بالفعل'),
                    ]
                )
            ),
        ]
    )]
    public function deleteSermon() {}

    #[OA\Get(
        path: '/sermons/search',
        operationId: 'searchSermons',
        tags: ['Sermons'],
        summary: 'Search and filter sermons',
        description: 'Returns a paginated, filtered list of sermons. Results are automatically scoped by the authenticated user\'s role: mosque managers see only their own sermons.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by sermon status',
                schema: new OA\Schema(type: 'string', enum: ['Pending', 'Archived', 'Rejected'])
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                required: false,
                description: 'Filter by sermon category',
                schema: new OA\Schema(type: 'string', enum: ['creed_faith', 'jurisprudence_rulings', 'ethics_conduct', 'contemporary_issues', 'occasions_seasons', 'other'], example: 'occasions_seasons')
            ),
            new OA\Parameter(
                name: 'mosque_manager_id',
                in: 'query',
                required: false,
                description: 'Filter by the mosque manager who submitted the sermon',
                schema: new OA\Schema(type: 'integer', example: 3)
            ),
            new OA\Parameter(
                name: 'region_manager_id',
                in: 'query',
                required: false,
                description: 'Filter by the admin who approved/rejected the sermon',
                schema: new OA\Schema(type: 'integer', example: 7)
            ),
            new OA\Parameter(
                name: 'speaker_name',
                in: 'query',
                required: false,
                description: 'Partial match on speaker name',
                schema: new OA\Schema(type: 'string', example: 'أحمد')
            ),
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: false,
                description: 'Full-text style search across title, content, and speaker name',
                schema: new OA\Schema(type: 'string', example: 'التوبة')
            ),
            new OA\Parameter(
                name: 'sermon_date_from',
                in: 'query',
                required: false,
                description: 'Filter sermons delivered on or after this date',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-01-01')
            ),
            new OA\Parameter(
                name: 'sermon_date_to',
                in: 'query',
                required: false,
                description: 'Filter sermons delivered on or before this date',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-06-30')
            ),
            new OA\Parameter(
                name: 'submitted_from',
                in: 'query',
                required: false,
                description: 'Filter by submission date (created_at), on or after',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-01-01')
            ),
            new OA\Parameter(
                name: 'submitted_to',
                in: 'query',
                required: false,
                description: 'Filter by submission date (created_at), on or before',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-06-30')
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort field and direction, format: field:direction. Allowed fields: sermon_date, created_at, title, status.',
                schema: new OA\Schema(type: 'string', example: 'sermon_date:asc')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Number of results per page (max 100)',
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Filtered sermons retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Sermons filtered successfully.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/Sermon')
                                ),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 4),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 52),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'خطأ في التحقق من البيانات'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function searchSermons() {}

    #[OA\Put(
        path: '/sermons/{id}/approve',
        operationId: 'approveSermon',
        tags: ['Sermons'],
        summary: 'Approve a sermon',
        description: 'Allows a super admin to approve a pending sermon.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sermon approved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تمت الموافقة على الخطبة بنجاح'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Sermon'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Sermon not found'),
        ]
    )]
    public function approveSermon() {}

    #[OA\Put(
        path: '/sermons/{id}/reject',
        operationId: 'rejectSermon',
        tags: ['Sermons'],
        summary: 'Reject a sermon',
        description: 'Allows a super admin to reject a sermon and provide notes.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['notes'],
                properties: [
                    new OA\Property(
                        property: 'notes',
                        type: 'string',
                        example: 'يرجى تعديل محتوى الخطبة وإعادة الإرسال.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sermon rejected',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم رفض الخطبة بنجاح'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Sermon'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Sermon not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function rejectSermon() {}

    // =========================================================================
    // GET /tameems
    // =========================================================================

    #[OA\Get(
        path: '/tameems',
        operationId: 'listTameems',
        tags: ['Tameems'],
        summary: 'List all circulars',
        description: 'Returns all tameems visible to the authenticated user.',
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
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب التعاميم بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Tameem')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function listTameems() {}

    // =========================================================================
    // POST /tameems
    // =========================================================================

    #[OA\Post(
        path: '/tameems',
        operationId: 'storeTameem',
        tags: ['Tameems'],
        summary: 'Send a circular',
        description: <<<DESC
        Sends a tameem to one or more mosque managers. (Region manager only.)
        - `sender_id` is resolved automatically from the auth token.
        - All IDs in `recipient_ids` must belong to users with `role = mosque_manager`.
        - Passing a non-mosque-manager ID returns a `422` validation error.
        DESC,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content', 'recipient_ids'],
                properties: [
                    new OA\Property(property: 'title',   type: 'string', example: 'تعميم بشأن صلاة التراويح'),
                    new OA\Property(property: 'content', type: 'string', example: 'يُرجى الالتزام بالمواعيد المحددة...'),
                    new OA\Property(
                        property: 'recipient_ids',
                        type: 'array',
                        description: 'IDs of mosque managers to receive this tameem.',
                        items: new OA\Items(type: 'integer'),
                        example: [3, 5, 8]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tameem sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم إرسال التعميم بنجاح'),
                        new OA\Property(property: 'data',    ref: '#/components/schemas/Tameem'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(
                response: 422,
                description: 'Validation error — e.g. a recipient ID does not belong to an allowed role',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'أحد المستلمين غير موجود أو ليس من الصلاحيات المسموح إرسال التعميم إليها.'),
                    ]
                )
            ),
        ]
    )]
    public function storeTameem() {}

    #[OA\Post(
        path: '/tameems/for-mosque',
        operationId: 'storeTameemForMosque',
        tags: ['Tameems'],
        summary: 'Send a circular to the mosque staff',
        description: <<<DESC
        Allows an authenticated mosque manager to send a tameem to the supervisors
        and teachers of their own mosque.
        - The mosque is derived automatically from the authenticated manager (`managedMosque`); no `mosque_id` is required.
        - All IDs in `recipient_ids` must belong to users with `role` = `halaqa_supervisor` or `teacher` who belong to the same mosque as the sender.
        - A recipient from another mosque, or with a different role, returns a `422` validation error.
        DESC,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content', 'recipient_ids'],
                properties: [
                    new OA\Property(property: 'title',   type: 'string', example: 'تعميم بشأن جدول الحلقات'),
                    new OA\Property(property: 'content', type: 'string', example: 'يُرجى الالتزام بالمواعيد المحددة...'),
                    new OA\Property(
                        property: 'recipient_ids',
                        type: 'array',
                        description: 'IDs of supervisors/teachers in the sender\'s mosque to receive this tameem.',
                        items: new OA\Items(type: 'integer'),
                        example: [12, 15]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tameem sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم إرسال التعميم بنجاح'),
                        new OA\Property(property: 'data',    ref: '#/components/schemas/Tameem'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — mosque_manager role required'),
            new OA\Response(
                response: 422,
                description: 'Validation error — e.g. a recipient is not a supervisor/teacher in your mosque',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'أحد المستلمين غير تابع لمسجدك.'),
                    ]
                )
            ),
        ]
    )]
    public function storeTameemForMosque() {}

    // =========================================================================
    // PUT /tameems/{id}
    // =========================================================================

    #[OA\Put(
        path: '/tameems/{id}',
        operationId: 'updateTameem',
        tags: ['Tameems'],
        summary: 'Update a circular',
        description: <<<DESC
        Updates an existing tameem. Only the original sender may update.
        - All fields are optional; only provided fields will be changed.
        - `recipient_ids` must all belong to users with `role = mosque_manager`.
        - Providing `recipient_ids` replaces the entire recipient list (sync).
        - Returns `403` if the authenticated user is not the sender.
        DESC,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 10
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title',   type: 'string', nullable: true, example: 'تعميم مُحدَّث بشأن مواعيد الصلاة'),
                    new OA\Property(property: 'content', type: 'string', nullable: true, example: 'يُعلم جميع الأئمة بالتعديلات الجديدة...'),
                    new OA\Property(
                        property: 'recipient_ids',
                        type: 'array',
                        nullable: true,
                        description: 'IDs of mosque managers. Replaces the full recipient list when provided.',
                        items: new OA\Items(type: 'integer'),
                        example: [3, 7]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tameem updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث التعميم بنجاح'),
                        new OA\Property(property: 'data',    ref: '#/components/schemas/Tameem'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — not the original sender'),
            new OA\Response(response: 404, description: 'Tameem not found'),
            new OA\Response(
                response: 422,
                description: 'Validation error — e.g. a recipient ID does not belong to a mosque manager',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'أحد المستلمين غير موجود أو ليس مدير مسجد.'),
                    ]
                )
            ),
        ]
    )]
    public function updateTameem() {}

    // =========================================================================
    // DELETE /tameems/{id}
    // =========================================================================

    #[OA\Delete(
        path: '/tameems/{id}',
        operationId: 'deleteTameem',
        tags: ['Tameems'],
        summary: 'Delete a circular',
        description: <<<DESC
        Permanently deletes a tameem and detaches all recipients.
        - Only the original sender may delete.
        - Returns `403` if the authenticated user is not the sender.
        DESC,
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 10
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tameem deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف التعميم بنجاح'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — not the original sender'),
            new OA\Response(response: 404, description: 'Tameem not found'),
        ]
    )]
    public function deleteTameem() {}

    // =========================================================================
    // GET /tameems/my-tameems
    // =========================================================================

    #[OA\Get(
        path: '/tameems/my-tameems',
        operationId: 'myTameems',
        tags: ['Tameems'],
        summary: 'Get received circulars',
        description: 'Returns all tameems received by the authenticated mosque manager.',
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
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب التعاميم الواردة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Tameem')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function myTameems() {}

    #[OA\Get(
        path: '/tameems/sent',
        operationId: 'sentTameems',
        tags: ['Tameems'],
        summary: 'Get sent circulars',
        description: 'Returns all tameems sent by the authenticated mosque manager.',
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
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب التعاميم الصادرة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Tameem')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function sentTameems() {}

    // =========================================================================
    // PATCH /tameems/{id}/read
    // =========================================================================

    #[OA\Patch(
        path: '/tameems/{id}/read',
        operationId: 'markTameemAsRead',
        tags: ['Tameems'],
        summary: 'Mark a circular as read',
        description: 'Marks the tameem as read for the authenticated mosque manager.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث حالة التعميم إلى مقروء'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Tameem not found'),
        ]
    )]
    public function markTameemAsRead() {}

    #[OA\Get(
        path: '/tameems/{id}',
        operationId: 'getTameemById',
        tags: ['Tameems'],
        summary: 'Get a circular by ID',
        description: 'Returns the details of a specific tameem by its ID.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب التعميم بنجاح'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Tameem'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Tameem not found'),
        ]
    )]
    public function getTameemById() {}
    // =========================================================================
    // Sermon Selections Endpoints
    // =========================================================================

    #[OA\Post(
        path: '/sermon-selections',
        operationId: 'storeSermonSelection',
        tags: ['Sermon Selections'],
        summary: 'Select an archived sermon for a specific Friday',
        description: 'Allows a mosque manager to select an approved sermon to be delivered on a specific Friday.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['sermon_id', 'friday_date'],
                    properties: [
                        new OA\Property(
                            property: 'sermon_id',
                            type: 'integer',
                            description: 'ID of the approved sermon to select',
                            example: 5
                        ),
                        new OA\Property(
                            property: 'friday_date',
                            type: 'string',
                            format: 'date',
                            description: 'The Friday date for which the sermon is selected',
                            example: '2026-07-18'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sermon selected successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم اختيار الخطبة بنجاح لإلقائها يوم الجمعة.'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/SermonSelection'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — only mosque managers can select sermons'),
            new OA\Response(response: 404, description: 'Sermon not found or not approved'),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'خطأ في التحقق من البيانات'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflict — sermon already selected for this Friday',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم اختيار خطبة لهذا اليوم مسبقاً.'),
                    ]
                )
            ),
        ]
    )]
    public function storeSermonSelection() {}
    #[OA\Get(
        path: '/sermon-selections/mine',
        operationId: 'mySermonSelections',
        tags: ['Sermon Selections'],
        summary: 'Get my sermon selections',
        description: 'Returns all sermon selections made by the authenticated mosque manager.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'friday_date_from',
                in: 'query',
                required: false,
                description: 'Filter by Friday date, on or after',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-01-01')
            ),
            new OA\Parameter(
                name: 'friday_date_to',
                in: 'query',
                required: false,
                description: 'Filter by Friday date, on or before',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-06-30')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم جلب اختياراتك بنجاح.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/SermonSelection')
                                ),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 30),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function mySermonSelections() {}

    #[OA\Get(
        path: '/sermon-selections/upcoming',
        operationId: 'upcomingSermonSelections',
        tags: ['Sermon Selections'],
        summary: 'Get upcoming sermon selections per mosque',
        description: 'Returns the selected sermon for each mosque for the upcoming Friday.',
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
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم جلب الخطبة المختارة لكل مسجد بنجاح.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/SermonSelection')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function upcomingSermonSelections() {}

    #[OA\Get(
        path: '/sermon-selections',
        operationId: 'indexSermonSelections',
        tags: ['Sermon Selections'],
        summary: 'Get all sermon selections',
        description: 'Returns a paginated list of all sermon selections. Admins can see all selections.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'mosque_manager_id',
                in: 'query',
                required: false,
                description: 'Filter by mosque manager ID',
                schema: new OA\Schema(type: 'integer', example: 3)
            ),
            new OA\Parameter(
                name: 'friday_date_from',
                in: 'query',
                required: false,
                description: 'Filter by Friday date, on or after',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-01-01')
            ),
            new OA\Parameter(
                name: 'friday_date_to',
                in: 'query',
                required: false,
                description: 'Filter by Friday date, on or before',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-06-30')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Number of results per page (max 100)',
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم جلب سجل الاختيارات بنجاح.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/SermonSelection')
                                ),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 65),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — only admins can view all selections'),
        ]
    )]
    public function indexSermonSelections() {}

    #[OA\Delete(
        path: '/sermon-selections/{id}',
        operationId: 'deleteSermonSelection',
        tags: ['Sermon Selections'],
        summary: 'Cancel a sermon selection',
        description: 'Allows a mosque manager to cancel their sermon selection for a Friday.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Selection cancelled successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تم إلغاء اختيار الخطبة بنجاح.'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — not the owner of the selection'),
            new OA\Response(response: 404, description: 'Sermon selection not found'),
            new OA\Response(
                response: 409,
                description: 'Conflict — selection cannot be cancelled because the Friday has passed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'لا يمكن إلغاء اختيار خطبة لتاريخ مضى.'),
                    ]
                )
            ),
        ]
    )]
    public function deleteSermonSelection() {}
}
