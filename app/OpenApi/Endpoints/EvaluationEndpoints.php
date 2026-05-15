<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class EvaluationEndpoints
{
    #[OA\Post(
        path: '/education/evaluations',
        operationId: 'storeEvaluation',
        tags: ['Evaluations'],
        summary: 'تقييم طالب',
        description: 'يقوم المعلم بتقييم طالب في الحلقة',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['halaqa_id', 'student_id'],
                properties: [
                    new OA\Property(property: 'halaqa_id', type: 'integer', example: 1),
                    new OA\Property(property: 'student_id', type: 'integer', example: 10),
                    new OA\Property(property: 'surah_name', type: 'string', example: 'الفاتحة'),
                    new OA\Property(property: 'from_ayah', type: 'integer', example: 1),
                    new OA\Property(property: 'to_ayah', type: 'integer', example: 7),
                    new OA\Property(property: 'score', type: 'integer', example: 90, nullable: true),
                    new OA\Property(property: 'notes', type: 'string', example: 'جيد جداً', nullable: true),
                    new OA\Property(property: 'evaluated_at', type: 'string', format: 'date-time', example: '2026-05-04 10:00:00'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم التقييم بنجاح',
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
}
