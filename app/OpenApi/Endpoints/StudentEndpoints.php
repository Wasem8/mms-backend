<?php

namespace App\OpenApi\Endpoints;

use Modules\Education\Http\Requests\StoreStudentRequest;
use OpenApi\Attributes as OA;

class StudentEndpoints
{
    #[OA\Get(
        path: '/education/students',
        operationId: 'listStudents',
        tags: ['Students'],
        summary: 'عرض قائمة الطلاب',
        description: 'يعيد قائمة الطلاب المفلترة: المشرف يرى طلاب مسجده، وولي الأمر يرى أبناءه.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب القائمة بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم استعادة قائمة الطلاب بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StudentResource')
                        ),
                        new OA\Property(property: 'pagination', type: 'object', ref: '#/components/schemas/Pagination')
                    ]
                )
            )
        ]
    )]
    public function index()
    {

    }

    #[OA\Post(
        path: '/education/students',
        operationId: 'storeStudent',
        tags: ['Students'],
        summary: 'تسجيل طالب جديد (من قبل ولي الأمر)',
        description: 'يسمح لولي الأمر بتسجيل ابنه في النظام مع تحديد المسجد المراد الالتحاق به. يتم إنشاء الحساب بحالة (غير نشط) حتى موافقة المشرف.',
        security: [['bearerAuth' => []]],
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
        summary: 'عرض بيانات طالب محدد',
        description: 'يعيد تفاصيل طالب معين حسب الصلاحيات (مشرف المسجد أو ولي الأمر).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'معرف الطالب',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب بيانات الطالب بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب بيانات الطالب بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/StudentResource'
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            nullable: true,
                            example: null
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function show()
    {
    }

    #[OA\Patch(
        path: '/education/students/{id}/approve',
        operationId: 'approveStudent',
        tags: ['Students'],
        summary: 'قبول طلب تسجيل طالب (للمشرف فقط)',
        description: 'يسمح لمشرف المسجد بتغيير حالة الطالب من (غير نشط) إلى (نشط) ليتمكن من الانضمام للحلقات.',
        security: [['bearerAuth' => []]],
        parameters: [
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
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function approve($id)
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
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function reject($id)
    {
    }
}


