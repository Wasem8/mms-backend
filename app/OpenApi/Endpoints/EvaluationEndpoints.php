<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class EvaluationEndpoints
{
    #[OA\Post(
        path: '/education/evaluations',
        operationId: 'storeEvaluation',
        tags: ['Evaluations'],
        summary: 'تقييم طالب (يدعم الحفظ الأوفلاين والـ Deduplication)',
        description: 'يقوم المعلم بتقييم طالب في الحلقة. يدعم معرّف العميل لمنع تكرار السجلات عند مشاكل الشبكة.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
            // 🎯 أضفنا الـ score هنا لأنه إجباري في الباك إند
                required: ['halaqa_id', 'student_id', 'score'],
                properties: [
                    // 🎯 الحقل الحاسم والجديد لنظام الـ Offline
                    new OA\Property(property: 'client_uuid', type: 'string', format: 'uuid', description: 'معرف فريد يرسله الموبايل لمنع تكرار التقييم عند إعادة المحاولة', example: '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d'),
                    new OA\Property(property: 'halaqa_id', type: 'integer', example: 1),
                    new OA\Property(property: 'student_id', type: 'integer', example: 10),
                    new OA\Property(property: 'surah_name', type: 'string', example: 'الفاتحة'),
                    new OA\Property(property: 'from_ayah', type: 'integer', example: 1),
                    new OA\Property(property: 'to_ayah', type: 'integer', example: 7),
                    new OA\Property(property: 'score', type: 'integer', example: 90),
                    new OA\Property(property: 'notes', type: 'string', example: 'جيد جداً', nullable: true),
                    new OA\Property(
                        property: 'voice_note_id',
                        type: 'integer',
                        nullable: true,
                        example: 15,
                        description: 'Uploaded voice note id'
                    ),

                    new OA\Property(
                        property: 'dimensions',
                        type: 'object',
                        nullable: true,
                        properties: [

                            new OA\Property(
                                property: 'tajweed',
                                type: 'string',
                                enum: ['excellent', 'good', 'needs_work'],
                                example: 'excellent'
                            ),

                            new OA\Property(
                                property: 'hifz',
                                type: 'string',
                                enum: ['excellent', 'good', 'needs_work'],
                                example: 'good'
                            ),

                            new OA\Property(
                                property: 'fluency',
                                type: 'string',
                                enum: ['excellent', 'good', 'needs_work'],
                                example: 'excellent'
                            ),

                            new OA\Property(
                                property: 'makharij',
                                type: 'string',
                                enum: ['excellent', 'good', 'needs_work'],
                                example: 'needs_work'
                            ),
                        ]
                    ),
                    new OA\Property(property: 'evaluated_at', type: 'string', format: 'date', description: 'تاريخ التقييم الفعلي من جهاز المعلم', example: '2026-06-05'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200, // أو 201
                description: 'تم التقييم بنجاح، أو تمت إعادة السجل القديم لتطابق الـ client_uuid',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تقييم الطالب بنجاح'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/EvaluationResource'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/education/supervisor/evaluations',
        operationId: 'getSupervisorEvaluations',
        tags: ['Evaluations'], // توحيد التاج
        summary: 'عرض تقييمات المسجد (للمشرف)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'halaqa_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة تقييمات المسجد',
                content: new OA\JsonContent(ref: '#/components/schemas/EvaluationListResponse')
            ),
            new OA\Response(response: 401, description: 'غير مصرح')
        ]
    )]
    public function indexForSupervisor() {}

    #[OA\Get(
        path: '/education/teacher/evaluations',
        operationId: 'getTeacherEvaluations',
        tags: ['Evaluations'], // توحيد التاج
        summary: 'عرض تقييمات المعلم (للمعلم)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة التقييمات الخاصة بالمعلم',
                content: new OA\JsonContent(ref: '#/components/schemas/EvaluationListResponse')
            ),
            new OA\Response(response: 401, description: 'غير مصرح')
        ]
    )]
    public function indexForTeacher() {}

    #[OA\Get(
        path: '/education/parent/evaluations',
        operationId: 'getParentEvaluations',
        tags: ['Evaluations'], // توحيد التاج
        summary: 'عرض تقييمات الأبناء (لولي الأمر)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'student_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة تقييمات الأبناء',
                content: new OA\JsonContent(ref: '#/components/schemas/EvaluationListResponse')
            ),
            new OA\Response(response: 401, description: 'غير مصرح')
        ]
    )]
    public function indexForParent() {}

    #[OA\Get(
        path: '/education/evaluations/{id}',
        operationId: 'getEvaluationById',
        tags: ['Evaluations'],
        summary: 'عرض تفاصيل تقييم محدد',
        description: 'يسمح بجلب بيانات تقييم واحد بالتفصيل باستخدام المعرف (ID). يتم التحقق من الصلاحيات تلقائياً.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف التقييم',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تفاصيل التقييم',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/EvaluationListResponse')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'التقييم غير موجود'),
            new OA\Response(response: 403, description: 'غير مصرح لك بعرض هذا التقييم')
        ]
    )]
    public function show() {}


    #[OA\Put(
        path: '/education/evaluations/{id}',
        operationId: 'updateEvaluation',
        tags: ['Evaluations'],
        summary: 'تعديل تقييم طالب',
        description: 'يسمح بتعديل درجة التقييم، الملاحظات، أو تفاصيل السورة والآيات. جميع حقول الطلب اختيارية.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف التقييم',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 10)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'score', type: 'integer', example: 98, nullable: true),
                    new OA\Property(property: 'notes', type: 'string', example: 'تم تحسين النطق في الآيات الأخيرة', nullable: true),
                    new OA\Property(property: 'surah_name', type: 'string', example: 'البقرة', nullable: true),
                    new OA\Property(property: 'from_ayah', type: 'integer', example: 1, nullable: true),
                    new OA\Property(property: 'to_ayah', type: 'integer', example: 20, nullable: true),
                    new OA\Property(
                        property: 'voice_note_id',
                        type: 'integer',
                        nullable: true,
                        example: 15
                    ),

                    new OA\Property(
                        property: 'dimensions',
                        type: 'object',
                        nullable: true,
                        properties: [

                            new OA\Property(
                                property: 'tajweed',
                                type: 'string',
                                enum: ['excellent', 'good', 'needs_work']
                            ),

                            new OA\Property(
                                property: 'hifz',
                                type: 'string',
                                enum: ['excellent', 'good', 'needs_work']
                            ),

                            new OA\Property(
                                property: 'fluency',
                                type: 'string',
                                enum: ['excellent', 'good', 'needs_work']
                            ),

                            new OA\Property(
                                property: 'makharij',
                                type: 'string',
                                enum: ['excellent', 'good', 'needs_work']
                            ),
                        ]
                    ),
                    new OA\Property(property: 'evaluated_at', type: 'string', format: 'date', example: '2026-05-09', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم التحديث بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث التقييم بنجاح'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/EvaluationResource')
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'خطأ في التحقق من البيانات (مثلاً رقم الآية "إلى" أصغر من "من")',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'The to ayah must be greater than or equal to from ayah.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'خطأ في الصلاحيات (التقييم لا يخص المعلم أو المسجد)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'غير مصرح لك بتعديل هذا التقييم')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'التقييم غير موجود')
        ]
    )]
    public function update()
    {
    }

    #[OA\Delete(
        path: '/education/evaluations/{id}',
        operationId: 'deleteEvaluation',
        tags: ['Evaluations'],
        summary: 'حذف تقييم',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 10))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم الحذف بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم حذف التقييم بنجاح'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'التقييم غير موجود',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'التقييم غير موجود')
                    ]
                )
            )
        ]
    )]
    public function destroy() {}
    #[OA\Post(
        path: '/education/uploads/audio',
        operationId: 'uploadVoiceNote',
        tags: ['Evaluations'],
        summary: 'Upload evaluation voice note',
        description: 'Uploads an audio file and returns voice_note_id to be attached later when creating or updating an evaluation.',
        security: [['bearerAuth' => []]],

        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/AcceptLanguageHeader'
            ),
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [

                        new OA\Property(
                            property: 'file',
                            type: 'string',
                            format: 'binary',
                            description: 'Audio file (mp3, wav, m4a, ogg)'
                        ),
                    ]
                )
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'Voice uploaded successfully',
                content: new OA\JsonContent(
                    properties: [

                        new OA\Property(
                            property: 'status',
                            type: 'boolean',
                            example: true
                        ),

                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Voice uploaded successfully'
                        ),

                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [

                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 15
                                ),

                                new OA\Property(
                                    property: 'url',
                                    type: 'string',
                                    example: 'https://example.com/storage/voice-notes/abc123.m4a'
                                ),
                            ]
                        ),

                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            example: null
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: 'Invalid file'
            ),

            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
        ]
    )]
    public function uploadVoice() {}


    #[OA\Get(
        path: '/education/evaluation-labels',
        operationId: 'getEvaluationLabels',
        tags: ['Evaluations'],
        summary: 'قائمة تسميات وقيم التقييم المتاحة',
        description: 'يجلب قائمة بجميع المفاتيح والقيم النصية المترجمة المعتمدة للتقييمات (مثل ممتاز، جيد، الخ).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب القائمة بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Evaluation labels retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'key', type: 'string', example: 'excellent'),
                                    new OA\Property(property: 'name', type: 'string', example: 'ممتاز')
                                ]
                            )
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح')
        ]
    )]
    public function getEvaluationLabels() {}

}
