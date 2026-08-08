<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class MosqueTaskEndpoints
{
    #[OA\Get(
        path: '/mosque/tasks',
        operationId: 'getMosqueTasks',
        tags: ['Mosque Tasks'],
        summary: 'List mosque tasks for a given date / {Mosque Manager Only}',
        description: 'Returns the operational tasks (صلاة وعبادة، نظافة، صيانة، فعالية، إداري) for the authenticated manager\'s mosque, filtered by date, status, and category. Includes a completion summary.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'date',
                in: 'query',
                required: false,
                description: 'Date to filter tasks by (YYYY-MM-DD). Defaults to today.',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-08-08')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by completion status.',
                schema: new OA\Schema(type: 'string', enum: ['completed', 'pending'])
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                required: false,
                description: 'Filter by task category.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['prayer_worship', 'cleaning', 'maintenance', 'activity', 'administrative']
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mosque tasks retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mosque tasks retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'tasks',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 12),
                                            new OA\Property(property: 'title', type: 'string', example: 'تأكيد جاهزية المسجد وصلاة الفجر'),
                                            new OA\Property(property: 'category', type: 'string', example: 'prayer_worship'),
                                            new OA\Property(property: 'category_label', type: 'string', example: 'صلاة وعبادة'),
                                            new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-08-08'),
                                            new OA\Property(property: 'due_time', type: 'string', nullable: true, example: '04:15'),
                                            new OA\Property(property: 'is_completed', type: 'boolean', example: true),
                                            new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', nullable: true, example: '2026-08-08T04:20:00Z'),
                                            new OA\Property(property: 'is_important', type: 'boolean', example: false),
                                            new OA\Property(property: 'notes', type: 'string', nullable: true, example: null),
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: 'summary',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'total', type: 'integer', example: 8),
                                        new OA\Property(property: 'completed', type: 'integer', example: 2),
                                        new OA\Property(property: 'percentage', type: 'integer', example: 25),
                                        new OA\Property(
                                            property: 'by_category',
                                            type: 'object',
                                            example: [
                                                'prayer_worship' => 2,
                                                'cleaning' => 1,
                                                'maintenance' => 1,
                                                'activity' => 1,
                                                'administrative' => 3,
                                            ]
                                        ),
                                    ]
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
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
            new OA\Response(response: 403, description: 'Forbidden — user is not a mosque manager or has no managed mosque'),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/mosque/tasks/date-tabs',
        operationId: 'getMosqueTaskDateTabs',
        tags: ['Mosque Tasks'],
        summary: 'Get task counts for quick date pills / {Mosque Manager Only}',
        description: 'Returns the number of tasks scheduled for today, tomorrow, the day after tomorrow, the coming Friday, and next week — used to render the date-selector pills shown at the top of the tasks page.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Date tabs retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Date tabs retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'today', type: 'integer', example: 8),
                                new OA\Property(property: 'tomorrow', type: 'integer', example: 3),
                                new OA\Property(property: 'day_after', type: 'integer', example: 2),
                                new OA\Property(property: 'friday', type: 'integer', example: 0),
                                new OA\Property(property: 'next_week', type: 'integer', example: 0),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function dateTabs() {}

    #[OA\Post(
        path: '/mosque/tasks',
        operationId: 'createMosqueTask',
        tags: ['Mosque Tasks'],
        summary: 'Create a mosque task (quick add or detailed) / {Mosque Manager Only}',
        description: 'Creates a new operational task for the manager\'s mosque. Used by both the "إضافة فورية" quick-add bar and the "إضافة مهمة مفصلة" modal.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'category'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'فحص التكييف', minLength: 2, maxLength: 255),
                    new OA\Property(
                        property: 'category',
                        type: 'string',
                        enum: ['prayer_worship', 'cleaning', 'maintenance', 'activity', 'administrative'],
                        example: 'maintenance'
                    ),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true, description: 'Defaults to today if omitted.', example: '2026-08-08'),
                    new OA\Property(property: 'due_time', type: 'string', nullable: true, example: '10:00'),
                    new OA\Property(property: 'is_important', type: 'boolean', nullable: true, example: false),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: null),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Mosque task created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تمت إضافة المهمة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 12),
                                new OA\Property(property: 'title', type: 'string', example: 'فحص التكييف'),
                                new OA\Property(property: 'category', type: 'string', example: 'maintenance'),
                                new OA\Property(property: 'category_label', type: 'string', example: 'صيانة'),
                                new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-08-08'),
                                new OA\Property(property: 'due_time', type: 'string', nullable: true, example: '10:00'),
                                new OA\Property(property: 'is_completed', type: 'boolean', example: false),
                                new OA\Property(property: 'completed_at', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'is_important', type: 'boolean', example: false),
                                new OA\Property(property: 'notes', type: 'string', nullable: true, example: null),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(property: 'data', type: 'object', example: ['category' => ['The selected category is invalid.']]),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function store() {}

    #[OA\Patch(
        path: '/mosque/tasks/{task}',
        operationId: 'updateMosqueTask',
        tags: ['Mosque Tasks'],
        summary: 'Update a mosque task / {Mosque Manager Only}',
        description: 'Partially updates a task belonging to the manager\'s mosque.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 12)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'فحص التكييف المركزي'),
                    new OA\Property(
                        property: 'category',
                        type: 'string',
                        enum: ['prayer_worship', 'cleaning', 'maintenance', 'activity', 'administrative']
                    ),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-08-09'),
                    new OA\Property(property: 'due_time', type: 'string', nullable: true, example: '11:00'),
                    new OA\Property(property: 'is_important', type: 'boolean'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mosque task updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث المهمة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 12),
                                new OA\Property(property: 'title', type: 'string', example: 'فحص التكييف المركزي'),
                                new OA\Property(property: 'category', type: 'string', example: 'maintenance'),
                                new OA\Property(property: 'category_label', type: 'string', example: 'صيانة'),
                                new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-08-09'),
                                new OA\Property(property: 'due_time', type: 'string', nullable: true, example: '11:00'),
                                new OA\Property(property: 'is_completed', type: 'boolean', example: false),
                                new OA\Property(property: 'completed_at', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'is_important', type: 'boolean', example: true),
                                new OA\Property(property: 'notes', type: 'string', nullable: true, example: null),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — task does not belong to the manager\'s mosque'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update() {}

    #[OA\Patch(
        path: '/mosque/tasks/{task}/toggle-complete',
        operationId: 'toggleMosqueTaskComplete',
        tags: ['Mosque Tasks'],
        summary: 'Toggle task completion / {Mosque Manager Only}',
        description: 'Flips is_completed for the task (the green checkmark on the task row). Sets or clears completed_at accordingly.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 12)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task status toggled successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث حالة المهمة'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 12),
                                new OA\Property(property: 'is_completed', type: 'boolean', example: true),
                                new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', nullable: true, example: '2026-08-08T10:05:00Z'),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Task not found'),
        ]
    )]
    public function toggleComplete() {}

    #[OA\Delete(
        path: '/mosque/tasks/{task}',
        operationId: 'deleteMosqueTask',
        tags: ['Mosque Tasks'],
        summary: 'Delete a mosque task / {Mosque Manager Only}',
        description: 'Permanently deletes a task belonging to the manager\'s mosque.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 12)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mosque task deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف المهمة'),
                        new OA\Property(property: 'data', type: 'object', example: []),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Task not found'),
        ]
    )]
    public function destroy() {}
}
