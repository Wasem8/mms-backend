<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class DashboardEndpoints
{
    #[OA\Get(
        path: '/dashboard/supervisor/stats',
        operationId: 'getSupervisorDashboardStats',
        tags: ['Dashboard'],
        summary: 'إحصائيات لوحة التحكم للمشرف التربوي مع فلترة',
        description: 'يعيد أرقام المؤشرات السريعة، الحضور الأسبوعي، منحنى تقدم الحفظ، المعلمين الأكثر إنجازاً، تقرير الغياب العام، وأحدث الأنشطة اللحظية. يدعم الفلترة الاختيارية بناءً على معرف الحلقة.',
        security: [['bearerAuth' => []]],

        // 🆕 إضافة بارامتر الفلترة بالـ halaqa_id في الـ Query URL
        parameters: [
            new OA\Parameter(
                name: 'halaqa_id',
                in: 'query',
                description: 'معرف الحلقة المراد فلترة البيانات بناءً عليها (اختياري)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب إحصائيات لوحة التحكم للمشرف التربوي بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب إحصائيات لوحة التحكم للمشرف التربوي بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                // 1. الـ Cards
                                new OA\Property(
                                    property: 'cards',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'total_students', type: 'integer', example: 10),
                                        new OA\Property(property: 'total_teachers', type: 'integer', example: 1),
                                        new OA\Property(property: 'total_halaqas', type: 'integer', example: 2),
                                        new OA\Property(property: 'attendance_today_percentage', type: 'string', example: '60%'),
                                    ]
                                ),
                                // 2. الحضور الأسبوعي
                                new OA\Property(
                                    property: 'weekly_attendance',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'day', type: 'string', example: 'Monday'),
                                            new OA\Property(property: 'percentage', type: 'integer', example: 60)
                                        ]
                                    )
                                ),
                                // 3. منحنى الحفظ والجودة
                                new OA\Property(
                                    property: 'quran_progress',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'month', type: 'string', example: 'May'),
                                            new OA\Property(property: 'average_score', type: 'number', format: 'float', example: 88.0),
                                            new OA\Property(property: 'total_evaluations', type: 'integer', example: 3)
                                        ]
                                    )
                                ),
                                // 4. المعلمون الأكثر إنجازاً
                                new OA\Property(
                                    property: 'top_teachers',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 4),
                                            new OA\Property(property: 'name', type: 'string', example: 'Teacher One'),
                                            new OA\Property(property: 'total_ayahs_reviewed', type: 'integer', example: 1093)
                                        ]
                                    )
                                ),
                                // 5. تقرير الغياب
                                new OA\Property(
                                    property: 'absenteeism_report',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'student_name', type: 'string', example: 'Student 1'),
                                            new OA\Property(property: 'halaqa_name', type: 'string', example: 'حلقة النور'),
                                            new OA\Property(property: 'absent_days_this_month', type: 'integer', example: 2)
                                        ]
                                    )
                                ),
                                // 6. الأنشطة الأخيرة
                                new OA\Property(
                                    property: 'recent_activities',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'title', type: 'string', example: 'تم تسجيل حضور حلقة حلقة النور'),
                                            new OA\Property(property: 'description', type: 'string', example: 'بواسطة Teacher One'),
                                            new OA\Property(property: 'time', type: 'string', example: '27 seconds ago'),
                                            new OA\Property(property: 'type', type: 'string', example: 'success')
                                        ]
                                    )
                                )
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح له الدخول - Unauthenticated'),
            new OA\Response(response: 403, description: 'ليس لديك صلاحية الوصول - Forbidden'),
            new OA\Response(response: 500, description: 'خطأ داخلي في السيرفر')
        ]
    )]
    public function getSupervisorStats()
    {

    }

    #[OA\Get(
        path: '/dashboard/supervisor/export-pdf',
        operationId: 'exportSupervisorDashboardPdf',
        tags: ['Dashboard'],
        summary: 'تنزيل تقرير لوحة التحكم بصيغة PDF',
        description: 'يقوم بتوليد وتنزيل ملف PDF يحتوي على الإحصائيات الحالية للمشرف التربوي مع دعم نفس فلاتر البحث التلقائية.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'halaqa_id',
                in: 'query',
                description: 'معرف الحلقة المحددة لتخصيص محتوى الـ PDF المتولد (اختياري)',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'يتم إرجاع الملف كملف باينري جاهز للتحميل (.pdf Binary Stream)'
            )
        ]
    )]
    public function exportPdfDocumentation() {}
}
