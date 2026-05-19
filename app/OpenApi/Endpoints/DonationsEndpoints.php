<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Donations',
    description: 'Manage donations — cash, in-kind, and online (Stripe). Covers UC-17, UC-35, UC-72, UC-73, UC-74.'
)]
class DonationsEndpoints
{
    // =========================================================================
    // Reusable schema
    // =========================================================================

    #[OA\Schema(
        schema: 'Donation',
        type: 'object',
        properties: [
            new OA\Property(property: 'id',               type: 'integer',  example: 101),
            new OA\Property(property: 'reference',        type: 'string',   example: 'REC-4892-2024'),
            new OA\Property(property: 'mosque_id',        type: 'integer',  example: 5),

            // ── Type & payment ────────────────────────────────────────────
            new OA\Property(
                property: 'type',
                type: 'string',
                enum: ['cash', 'in_kind'],
                example: 'cash'
            ),
            new OA\Property(
                property: 'payment_method',
                type: 'string',
                enum: ['cash', 'stripe'],
                example: 'cash'
            ),

            // ── Amounts ───────────────────────────────────────────────────
            new OA\Property(property: 'amount',           type: 'number',  format: 'float', nullable: true, example: 5000),
            new OA\Property(property: 'item_description', type: 'string',  nullable: true,  example: null),

            // ── Donor ─────────────────────────────────────────────────────
            new OA\Property(property: 'donor_name',  type: 'string', nullable: true, example: 'أحمد عبد الله المحمود'),
            new OA\Property(property: 'notes',       type: 'string', nullable: true, example: 'تبرع مخصص لدعم صيانة المكيفات'),

            // ── Relations ─────────────────────────────────────────────────
            new OA\Property(property: 'campaign_id',    type: 'integer', nullable: true, example: 12),
            new OA\Property(property: 'campaign_title', type: 'string',  nullable: true, example: 'صيانة أجهزة التكييف'),
            new OA\Property(property: 'mosque_need_id', type: 'integer', nullable: true, example: null),

            // ── Attachment ────────────────────────────────────────────────

            // ── Lifecycle ─────────────────────────────────────────────────
            new OA\Property(
                property: 'status',
                type: 'string',
                enum: ['pending', 'completed'],
                example: 'completed'
            ),
            new OA\Property(property: 'created_at',   type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at',   type: 'string', format: 'date-time'),
        ]
    )]
    public function schemaDonation() {}

    // =========================================================================
    // GET /mosques/{mosqueId}/donations
    // Screen 1 — donation log table with search & filter
    // =========================================================================

    #[OA\Get(
        path: '/mosques/{mosqueId}/donations',
        operationId: 'listDonations',
        tags: ['Donations'],
        summary: 'List donations for a mosque',
        description: 'Returns a paginated list of donations. Supports filtering by donor name, type, status, and campaign.',
        parameters: [
            new OA\Parameter(name: 'mosqueId', in: 'path', required: true,  schema: new OA\Schema(type: 'integer'), example: 5),
            new OA\Parameter(name: 'search',   in: 'query', required: false, schema: new OA\Schema(type: 'string'),  description: 'Search by donor name'),
            new OA\Parameter(name: 'type',     in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['cash', 'in_kind'])),
            new OA\Parameter(name: 'status',   in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'completed'])),
            new OA\Parameter(name: 'campaign', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'Filter by campaign ID'),
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
                            items: new OA\Items(ref: '#/components/schemas/Donation')
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page',    type: 'integer', example: 12),
                                new OA\Property(property: 'per_page',     type: 'integer', example: 10),
                                new OA\Property(property: 'total',        type: 'integer', example: 120),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function listDonations() {}

    #[OA\Get(
        path: '/donations/mine',
        operationId: 'listMyDonations',
        tags: ['Donations'],
        summary: 'List authenticated user donations',
        description: 'Returns the authenticated donor\'s donation history. Requires bearer token.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search',   in: 'query', required: false, schema: new OA\Schema(type: 'string'),  description: 'Search by donor name'),
            new OA\Parameter(name: 'type',     in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['cash', 'in_kind'])),
            new OA\Parameter(name: 'status',   in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'completed'])),
            new OA\Parameter(name: 'campaign', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'Filter by campaign ID'),
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
                            items: new OA\Items(ref: '#/components/schemas/Donation')
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page',    type: 'integer', example: 4),
                                new OA\Property(property: 'per_page',     type: 'integer', example: 10),
                                new OA\Property(property: 'total',        type: 'integer', example: 27),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function listMyDonations() {}

    // =========================================================================
    // GET /mosques/{mosqueId}/donations/summary
    // Screen 3 — "ملخص اليوم" panel
    // =========================================================================

    #[OA\Get(
        path: '/mosques/{mosqueId}/donations/summary',
        operationId: 'getDonationSummary',
        tags: ['Donations'],
        summary: 'Daily summary',
        description: 'Returns today\'s total collected amount and number of operations — shown in the "ملخص اليوم" panel on the add donation screen.',
        parameters: [
            new OA\Parameter(name: 'mosqueId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 5),
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
                                    property: 'total_today',
                                    type: 'number',
                                    format: 'float',
                                    example: 12450,
                                    description: 'Sum of completed cash donations today'
                                ),
                                new OA\Property(
                                    property: 'operations_count',
                                    type: 'integer',
                                    example: 8,
                                    description: 'Number of completed donations today'
                                ),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getDonationSummary() {}

    // =========================================================================
    // GET /mosques/{mosqueId}/donations/chart
    // Screen 1 — monthly distribution bar chart
    // =========================================================================

    #[OA\Get(
        path: '/mosques/{mosqueId}/donations/chart',
        operationId: 'getDonationChart',
        tags: ['Donations'],
        summary: 'Monthly distribution chart',
        description: 'Returns this month\'s donation totals grouped by type (cash / in_kind) for the bar chart.',
        parameters: [
            new OA\Parameter(name: 'mosqueId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 5),
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
                                new OA\Property(property: 'cash',    type: 'number', format: 'float', example: 45000),
                                new OA\Property(property: 'in_kind', type: 'number', format: 'float', example: 12000),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getDonationChart() {}

    // =========================================================================
    // GET /donations/{id}
    // Screen 2 — donation detail
    // =========================================================================

    #[OA\Get(
        path: '/donations/{id}',
        operationId: 'getDonation',
        tags: ['Donations'],
        summary: 'Get a donation',
        description: 'Returns full donation detail including reference, donor, campaign, attachment, and received_by.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 101),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Donation'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function getDonation() {}


    // ─── POST /donations/online ───────────────────────────────────────────────────

    #[OA\Post(
        path: '/donations/online',
        operationId: 'storeOnlineDonation',
        tags: ['Donations'],
        summary: 'إنشاء تبرع إلكتروني (Stripe)',
        description: <<<DESC
    يُنشئ طلب تبرع عبر Stripe ويُعيد `client_secret` للواجهة الأمامية لإتمام الدفع.

    - **العملة:** USD دائماً — يُحدِّدها الخادم تلقائياً، لا يُرسلها العميل.
    - **base_amount:** يُحسب تلقائياً (amount × exchange_rate) ويُخزَّن على التبرع.
    - **الحالة الأولية:** `pending` — تتغير إلى `completed` عبر Stripe Webhook تلقائياً.
    - **التوثيق:** اختياري — الزوار يمكنهم التبرع بدون توكن.
    DESC,
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['mosque_id', 'donation_type', 'amount'],
                    properties: [
                        new OA\Property(
                            property: 'mosque_id',
                            type: 'integer',
                            example: 5
                        ),
                        new OA\Property(
                            property: 'donation_type',
                            type: 'string',
                            enum: ['cash'],
                            example: 'cash',
                            description: 'نوع التبرع'
                        ),
                        new OA\Property(
                            property: 'amount',
                            type: 'number',
                            format: 'float',
                            example: 50,
                            description: 'المبلغ بالدولار الأمريكي (USD). مطلوب عند donation_type = cash'
                        ),
                        new OA\Property(
                            property: 'item_description',
                            type: 'string',
                            nullable: true,
                            description: 'وصف الصنف. مطلوب عند donation_type = in_kind'
                        ),
                        new OA\Property(
                            property: 'donor_name',
                            type: 'string',
                            nullable: true,
                            example: 'فاعل خير'
                        ),
                        new OA\Property(
                            property: 'campaign_id',
                            type: 'integer',
                            nullable: true,
                            example: 12
                        ),
                        new OA\Property(
                            property: 'mosque_need_id',
                            type: 'integer',
                            nullable: true
                        ),
                        // payment_method & currency مُحذوفان — يُحدِّدهما الخادم تلقائياً
                        // user_id مُحذوف — يُؤخذ من التوكن إن وُجد
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'تم إنشاء طلب التبرع بنجاح — في انتظار تأكيد الدفع من Stripe',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'تم إنشاء طلب التبرع بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            allOf: [
                                new OA\Schema(ref: '#/components/schemas/Donation'),
                                new OA\Schema(
                                    properties: [
                                        new OA\Property(
                                            property: 'client_secret',
                                            type: 'string',
                                            example: 'pi_3OxXxx_secret_yyy',
                                            description: 'يُرسَل إلى Stripe.js في الواجهة الأمامية لإتمام عملية الدفع. لا يُخزَّن في قاعدة البيانات.'
                                        ),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'خطأ في التحقق من صحة البيانات'),
            new OA\Response(response: 503, description: 'سعر الصرف غير مُهيَّأ — يجب على المدير ضبطه من لوحة التحكم'),
        ]
    )]
    public function storeOnline() {}


    // ─── POST /donations/admin/cash ───────────────────────────────────────────────

    #[OA\Post(
        path: '/donations/admin/cash',
        operationId: 'storeCashDonation',
        tags: ['Donations'],
        summary: 'إضافة تبرع نقدي أو عيني يدوياً (لمدير المسجد)',
        description: <<<DESC
    يُسجِّل مدير المسجد التبرعات النقدية والعينية المستلمة يدوياً.

    - **العملة:** SYP دائماً — يُحدِّدها الخادم تلقائياً، لا يُرسلها العميل.
    - **exchange_rate:** يُطبَّق 1.0 (لا تحويل مطلوب) وbase_amount = amount.
    - **الحالة:** تُضبط على `completed` فوراً ويُضاف base_amount إلى collected_amount في نفس العملية.
    - **التوثيق:** إلزامي — يجب أن يكون المستخدم مديراً (Admin).
    DESC,
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['mosque_id', 'donation_type', 'amount'],
                    properties: [
                        new OA\Property(
                            property: 'mosque_id',
                            type: 'integer',
                            example: 5
                        ),
                        new OA\Property(
                            property: 'donation_type',
                            type: 'string',
                            enum: ['cash', 'in_kind'],
                            example: 'cash',
                            description: 'نوع التبرع'
                        ),
                        new OA\Property(
                            property: 'amount',
                            type: 'number',
                            format: 'float',
                            example: 500000,
                            description: 'المبلغ بالليرة السورية (SYP). مطلوب عند donation_type = cash'
                        ),
                        new OA\Property(
                            property: 'item_description',
                            type: 'string',
                            nullable: true,
                            description: 'وصف الصنف. مطلوب عند donation_type = in_kind'
                        ),
                        new OA\Property(
                            property: 'donor_name',
                            type: 'string',
                            nullable: true,
                            example: 'فاعل خير'
                        ),
                        new OA\Property(
                            property: 'donation_date',
                            type: 'string',
                            format: 'date',
                            nullable: true,
                            description: 'تاريخ استلام التبرع (اختياري — الافتراضي: اليوم)'
                        ),
                        new OA\Property(
                            property: 'campaign_id',
                            type: 'integer',
                            nullable: true,
                            example: 12
                        ),
                        new OA\Property(
                            property: 'mosque_need_id',
                            type: 'integer',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'user_id',
                            type: 'integer',
                            nullable: true,
                            description: 'معرف المستخدم إذا كان المتبرع مسجلاً في النظام'
                        ),
                        // payment_method & currency مُحذوفان — يُحدِّدهما الخادم تلقائياً
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'تم حفظ التبرع وإضافته إلى الحملة أو الاحتياج فوراً',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'تم حفظ التبرع النقدي بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/Donation'
                            // لا يوجد client_secret — الدفع نقدي ومؤكد فوراً
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح — التوكن مفقود أو منتهي الصلاحية'),
            new OA\Response(response: 403, description: 'مرفوض — يجب أن تكون مديراً للمسجد'),
            new OA\Response(response: 422, description: 'خطأ في التحقق من صحة البيانات'),
        ]
    )]
    public function storeCash() {}







    // =========================================================================
    // PUT /donations/{id}
    // =========================================================================

    #[OA\Post(
        path: '/donations/{id}',
        operationId: 'updateDonation',
        tags: ['Donations'],
        summary: 'Update a donation',
        description: 'Partial update. Send as multipart/form-data when replacing the attachment. Add `_method=PUT` for clients that do not support PUT with multipart.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 101),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(property: '_method',         type: 'string',  example: 'PUT'),
                        new OA\Property(property: 'donor_name',      type: 'string',  nullable: true),
                        new OA\Property(property: 'amount',          type: 'number',  format: 'float', nullable: true),
                        new OA\Property(property: 'item_description', type: 'string',  nullable: true),
                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            enum: ['pending', 'completed'],
                            nullable: true
                        ),
                        new OA\Property(property: 'campaign_id',     type: 'integer', nullable: true),
                        new OA\Property(property: 'mosque_need_id',  type: 'integer', nullable: true),
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
                        new OA\Property(property: 'message', type: 'string',  example: 'Success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Donation'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function updateDonation() {}

    // =========================================================================
    // DELETE /donations/{id}
    // Screen 2 — "حذف السجل" action
    // =========================================================================

    #[OA\Delete(
        path: '/donations/{id}',
        operationId: 'deleteDonation',
        tags: ['Donations'],
        summary: 'Delete a donation',
        description: 'Soft-deletes the donation and removes its attachment from Supabase storage. Also reverses the campaign collected_amount if the donation was completed.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 101),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',  type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'تم حذف السجل بنجاح.'),
                        new OA\Property(property: 'data',    nullable: true,  example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function deleteDonation() {}

    // =========================================================================
    // POST /stripe/webhook  — internal, not called by frontend
    // =========================================================================

    #[OA\Post(
        path: '/stripe/webhook',
        operationId: 'stripeWebhook',
        tags: ['Donations'],
        summary: 'Stripe webhook',
        description: 'Called by Stripe after a payment_intent.succeeded or payment_intent.payment_failed event. Verifies the signature and marks the donation as completed. Do not call this endpoint manually.',
        parameters: [
            new OA\Parameter(
                name: 'Stripe-Signature',
                in: 'header',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'HMAC signature sent by Stripe — verified against STRIPE_WEBHOOK_SECRET'
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Webhook handled'),
            new OA\Response(response: 400, description: 'Invalid signature'),
        ]
    )]
    public function stripeWebhook() {}



    #[OA\Tag(
        name: 'Settings',
        description: 'System-wide settings management. Restricted to super_admin role.'
    )]

    #[OA\Schema(
        schema: 'Setting',
        type: 'object',
        properties: [
            new OA\Property(property: 'key',   type: 'string', example: 'usd_to_syp_rate'),
            new OA\Property(property: 'value', type: 'string', example: '14500'),
        ]
    )]
    public function schemaSetting() {}



    #[OA\Get(
        path: '/settings',
        operationId: 'getSettings',
        tags: ['Settings'],
        summary: 'List all settings',
        description: 'Returns all system settings as key-value pairs. Restricted to `super_admin`.',
        //security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Setting'),
                    example: [
                        ['key' => 'usd_to_syp_rate', 'value' => '14500'],
                        ['key' => 'another_setting',  'value' => 'some_value'],
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — requires super_admin role'),
        ]
    )]
    public function getSettings() {}
    #[OA\Get(
        path: '/donations/{id}/receipt',
        operationId: 'downloadDonationReceipt',
        tags: ['Donations'],
        summary: 'تحميل إيصال التبرع (PDF)',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 101
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ملف PDF جاهز للتحميل',
                headers: [
                    new OA\Header(
                        header: 'Content-Disposition',
                        description: 'attachment; filename="receipt-REC-4892-2024.pdf"',
                        schema: new OA\Schema(type: 'string')
                    ),
                ],
                content: new OA\MediaType(mediaType: 'application/pdf')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'التبرع غير موجود'),
        ]
    )]
    public function downloadReceipt() {}
    // =========================================================================
    // PUT /settings/exchange-rate
    // Updates the USD → SYP exchange rate used in online donations
    // =========================================================================

    #[OA\Put(
        path: '/settings/exchange-rate',
        operationId: 'updateExchangeRate',
        tags: ['Settings'],
        summary: 'Update USD → SYP exchange rate',
        description: <<<DESC
        Updates the `usd_to_syp_rate` setting used to convert online Stripe donations (USD) into
        the base currency (SYP) before crediting campaigns and mosque needs.

        - Clears the cached rate immediately so subsequent donations use the new value.
        - Restricted to `super_admin` only.
        - If this setting is absent or the cache is stale, the `POST /donations/online` endpoint
          returns **503** until the rate is configured here.
        DESC,
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['rate'],
                properties: [
                    new OA\Property(
                        property: 'rate',
                        type: 'number',
                        format: 'float',
                        minimum: 1,
                        maximum: 10000000,
                        example: 14500,
                        description: 'New exchange rate: 1 USD = {rate} SYP'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Exchange rate updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message',  type: 'string',  example: 'Exchange rate updated successfully.'),
                        new OA\Property(property: 'key',      type: 'string',  example: 'usd_to_syp_rate'),
                        new OA\Property(property: 'new_rate', type: 'number',  format: 'float', example: 14500),
                        new OA\Property(property: 'unit',     type: 'string',  example: '1 USD = 14,500 SYP'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error — rate is missing, non-numeric, or out of bounds',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The rate field is required.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'rate',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['The rate must be at least 1.']
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — requires super_admin role'),
        ]
    )]
    public function updateExchangeRate() {}
}
