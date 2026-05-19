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

    #[OA\Get(
        path: '/sermons',
        operationId: 'listSermons',
        tags: ['Sermons'],
        summary: 'List all sermons',
        description: 'Returns all sermons regardless of status. Accessible to authenticated users.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب جميع الخطب بنجاح'),
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
    public function listSermons() {}

    // =========================================================================
    // GET /sermons/pending
    // =========================================================================

    #[OA\Get(
        path: '/sermons/pending',
        operationId: 'listPendingSermons',
        tags: ['Sermons'],
        summary: 'List pending sermons',
        description: 'Returns sermons awaiting approval by a region manager.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب الخطب المعلقة بنجاح'),
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
    public function listPendingSermons() {}

    // =========================================================================
    // POST /sermons
    // =========================================================================

    #[OA\Post(
        path: '/sermons',
        operationId: 'storeSermon',
        tags: ['Sermons'],
        summary: 'Upload a sermon',
        description: <<<DESC
        Mosque manager uploads a new sermon. Status is set to `pending` until approved by a region manager.
        - Attachments are optional (PDF, audio, etc.) — max 10MB each.
        - `mosque_manager_id` is taken from the auth token automatically.
        DESC,
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'content'],
                    properties: [
                        new OA\Property(property: 'title',   type: 'string', example: 'خطبة الجمعة - الصبر'),
                        new OA\Property(property: 'content', type: 'string', example: 'الحمد لله...'),
                        new OA\Property('speaker_name', type: 'string', example: 'الشيخ محمد'),
                        new OA\Property('sermon_date', type: 'string', format: 'date', example: '2024-06-28'),
                        new OA\Property(
                            property: 'attachments[]',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            description: 'Optional files — max 10MB each'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Sermon uploaded — awaiting approval',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم رفع الخطبة بنجاح وبانتظار الاعتماد'),
                        new OA\Property(property: 'data',    ref: '#/components/schemas/Sermon'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function storeSermon() {}

    // =========================================================================
    // PUT /sermons/{id}/approve
    // =========================================================================

    #[OA\Put(
        path: '/sermons/{id}/approve',
        operationId: 'approveSermon',
        tags: ['Sermons'],
        summary: 'Approve a sermon',
        description: 'Region manager approves a pending sermon. Optional notes can be attached.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'خطبة ممتازة، جزاك الله خيراً'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sermon approved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم اعتماد الخطبة بنجاح'),
                        new OA\Property(property: 'data',    ref: '#/components/schemas/Sermon'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Sermon not found'),
        ]
    )]
    public function approveSermon() {}

    // =========================================================================
    // PUT /sermons/{id}/reject  — route commented out but documented
    // =========================================================================

    #[OA\Put(
        path: '/sermons/{id}/reject',
        operationId: 'rejectSermon',
        tags: ['Sermons'],
        summary: 'Reject a sermon',
        description: 'Region manager rejects a pending sermon. Notes should explain the reason.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'يرجى مراجعة المحتوى وإعادة الرفع'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sermon rejected',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'تم رفض الخطبة بنجاح'),
                        new OA\Property(property: 'data',    ref: '#/components/schemas/Sermon'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Sermon not found'),
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
}
