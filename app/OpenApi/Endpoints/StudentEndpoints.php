<?php

namespace App\OpenApi\Endpoints;

use App\Support\ApiResponse;
use Modules\Education\Http\Requests\StoreStudentRequest;
use Modules\Education\Transformers\StudentResource;
use OpenApi\Attributes as OA;

class StudentEndpoints
{
    #[OA\Get(
        path: '/education/students',
        operationId: 'getStudentsList',
        tags: ['Students'],
        summary: 'عرض قائمة الطلاب مع الفلترة',
        description: 'يمكن الفلترة حسب الحالة باستخدام query parameter: ?status=active أو pending أو rejected',
        security: [['bearerAuth' => []]],
        parameters: [

            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'فلترة الطلاب حسب الحالة',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['active', 'pending', 'rejected'],
                    example: 'active'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة الطلاب',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Student list retrieved successfully / تم جلب القائمة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StudentResource')
                        ),
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/Pagination')
                    ]
                )
            )
        ]
    )]
    public function index() {
        // الكود كما هو
    }

    #[OA\Post(
        path: '/education/students',
        operationId: 'storeStudent',
        tags: ['Students'],
        summary: 'تسجيل طالب جديد (من قبل ولي الأمر)',
        description: 'يسمح لولي الأمر بتسجيل ابنه في النظام مع تحديد المسجد المراد الالتحاق به. يتم إنشاء الحساب بحالة (غير نشط) حتى موافقة المشرف.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'gender', 'mosque_id'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', description: 'الاسم الأول للطالب', example: 'أحمد'),
                    new OA\Property(property: 'last_name', type: 'string', description: 'اسم العائلة', example: 'محمد'),
                    new OA\Property(property: 'mosque_id', type: 'integer', description: 'معرف المسجد المراد التسجيل فيه', example: 1),
                    new OA\Property(property: 'date_of_birth', type: 'string', format: 'date', description: 'تاريخ الميلاد', example: '2015-05-15'),
                    new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female'], description: 'الجنس', example: 'male'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم تسجيل الطلب بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تسجيل بيانات الطالب بنجاح، يرجى انتظار موافقة مشرف الحلقات لتفعيل الحساب.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/StudentResource'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function store(StoreStudentRequest $request)
    {
    }

    #[OA\Get(
        path: '/education/students/{id}',
        operationId: 'showStudent',
        tags: ['Students'],
        summary: 'عرض بيانات طالب محدد (تفصيلي)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),

            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم بنجاح',
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
                            example: 'تم جلب بيانات الطالب بنجاح.'
                        ),

                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/StudentDetailResource'
                        ),

                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            example: null
                        ),
                    ]
                )
            )
        ]
    )]
    public function show() {}

    #[OA\Patch(
        path: '/education/students/{id}/approve',
        operationId: 'approveStudent',
        tags: ['Students'],
        summary: 'قبول طلب تسجيل طالب (للمشرف فقط)',
        description: 'يسمح لمشرف المسجد بتغيير حالة الطالب من (غير نشط) إلى (نشط) ليتمكن من الانضمام للحلقات.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'معرف الطالب المراد قبوله',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم قبول الطالب بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم قبول الطالب بنجاح وتفعيل حسابه.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/StudentResource'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'خطأ في منطق الطلب (Bad Request)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            oneOf: [
                                new OA\Schema(example: 'هذا الطالب مفعل مسبقاً.'),
                                new OA\Schema(example: 'لا يمكن قبول طالب مرفوض بالفعل.'),
                            ]
                        ),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function approve()
    {

    }

    #[OA\Patch(
        path: '/education/students/{id}/reject',
        operationId: 'rejectStudent',
        tags: ['Students'],
        summary: 'رفض طلب تسجيل طالب (للمشرف فقط)',
        description: 'يسمح لمشرف المسجد برفض طلب انضمام طالب، مما يحول حالته إلى (مرفوض).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'معرف الطالب المراد رفضه',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم رفض الطالب بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم رفض طلب تسجيل الطالب.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/StudentResource'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'خطأ في منطق الطلب (Bad Request)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            oneOf: [
                                new OA\Schema(example: 'لا يمكن رفض طالب مقبول بالفعل.'),
                                new OA\Schema(example: 'هذا الطلب مرفوض مسبقاً.')
                            ]
                        ),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function reject()
    {

    }


    #[OA\Post(
        path: '/education/students/{id}/transfer',
        operationId: 'transferStudent',
        tags: ['Students'],
        summary: 'نقل طالب من حلقة إلى أخرى في نفس المسجد',
        description: 'يقوم بنقل الطالب من حلقة محددة إلى حلقة جديدة، مع التحقق من وجوده في الحلقة القديمة وتبعية الحلقات للمسجد.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptLanguageHeader'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'معرف الطالب المراد نقله',
                schema: new OA\Schema(type: 'integer', example: 3)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['from_halaqa_id', 'to_halaqa_id'],
                properties: [
                    new OA\Property(property: 'from_halaqa_id', type: 'integer', description: 'ID الحلقة الحالية للطالب', example: 1),
                    new OA\Property(property: 'to_halaqa_id', type: 'integer', description: 'ID الحلقة الجديدة المراد النقل إليها', example: 2),
                ]
            )
        ),
        responses: [
            // 1. حالة النجاح
            new OA\Response(
                response: 200,
                description: 'تمت عملية النقل بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم نقل الطالب بنجاح إلى حلقة (حلقة التميز)'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 3),
                                new OA\Property(property: 'full_name', type: 'string', example: 'أحمد محمد علي'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(
                                    property: 'current_halaqats',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 2),
                                            new OA\Property(property: 'name', type: 'string', example: 'حلقة التميز'),
                                            new OA\Property(property: 'teacher_name', type: 'string', example: 'الشيخ خالد'),
                                            new OA\Property(property: 'joined_at', type: 'string', example: '2026-05-08 14:00:00')
                                        ]
                                    )
                                ),

                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),

            // 2. خطأ: الطالب غير موجود في الحلقة القديمة
            new OA\Response(
                response: 400,
                description: 'خطأ في منطق النقل (مثلاً الطالب ليس في الحلقة القديمة)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'عذراً، الطالب غير مسجل في الحلقة القديمة التي اخترتها، يرجى التأكد من البيانات.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),

            // 3. خطأ: الـ Validation (مدخلات غير صحيحة)
            new OA\Response(
                response: 422,
                description: 'خطأ في التحقق من البيانات المرسلة',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The to halaqa id field is required.'),
                        new OA\Property(
                            property: 'errors',
                            properties: [
                                new OA\Property(property: 'to_halaqa_id', type: 'array', items: new OA\Items(type: 'string', example: 'يجب أن يكون حقل الحلقة الجديدة مختلفاً عن الحلقة القديمة.'))
                            ]
                        ),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),

            // 4. خطأ: الصلاحيات أو عدم الوجود
            new OA\Response(response: 404, description: 'الطالب غير موجود أو لا يتبع لمسجدك'),
            new OA\Response(response: 401, description: 'غير مصرح لك (Unauthenticated)')
        ]
    )]
    public function transfer($id)
    {
        // الكود كما هو
    }
}


