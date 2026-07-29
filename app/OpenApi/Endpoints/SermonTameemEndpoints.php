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

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'content', 'speaker_name', 'sermon_date'],
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
                    ]
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
        path: '/sermons/{id}',
        operationId: 'getSermonById',
        tags: ['Sermons'],
        summary: 'Get sermon by ID',
        description: 'Returns the details of a specific sermon.',
        security: [['bearerAuth' => []]],
        parameters: [
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
        description: 'Returns all sermons awaiting approval.',
        security: [['bearerAuth' => []]],
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
                    ]
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
        description: 'Returns approved, rejected, or completed sermons.',
        security: [['bearerAuth' => []]],
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
                    ]
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

    #[OA\Get(
        path: '/sermons/search',
        operationId: 'searchSermons',
        tags: ['Sermons'],
        summary: 'Search and filter sermons',
        description: 'Returns a paginated, filtered list of sermons. Results are automatically scoped by the authenticated user\'s role: mosque managers see only their own sermons.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by sermon status',
                schema: new OA\Schema(type: 'string', enum: ['Pending', 'Archived', 'Rejected'])
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
        Sends a tameem to one or more mosque managers.
        - `sender_id` is resolved automatically from the auth token.
        - All IDs in `recipient_ids` must belong to users with `role = mosque_manager`.
        - Passing a non-mosque-manager ID returns a `422` validation error.
        DESC,
        security: [['bearerAuth' => []]],
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
                description: 'Validation error — e.g. a recipient ID does not belong to a mosque manager',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'أحد المستلمين غير موجود أو ليس مدير مسجد.'),
                    ]
                )
            ),
        ]
    )]
    public function storeTameem() {}

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
}
