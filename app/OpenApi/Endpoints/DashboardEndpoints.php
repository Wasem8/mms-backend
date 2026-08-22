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
    public function getSupervisorStats() {}

    #[OA\Get(
        path: '/dashboard/supervisor/export-pdf',
        operationId: 'exportSupervisorDashboardPdf',
        tags: ['Reports'],
        summary: 'تصدير تقرير المشرف الموحد PDF',
        description: 'توليد وجلب رابط تحميل تقرير PDF شامل وموحد لكل حلقات المسجد بدون فلاتر (يدعم دخول المشرف أو مدير المسجد بكاش منفصل لكل مستخدم)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: false,
                description: 'نوع الرد المطلوب',
                schema: new OA\Schema(type: 'string', default: 'application/json')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم توليد رابط التقرير بنجاح (سواء من الكاش أو جديد)',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'status', type: 'boolean', example: true),
                                new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء تقرير المشرف الموحد بنجاح'),
                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'url', type: 'string', format: 'url', example: 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/sign/reports/supervisor-reports/1/general_1781780709.pdf?token=...'),
                                        new OA\Property(property: 'cached', type: 'boolean', example: false)
                                    ]
                                ),
                                new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                            ]
                        )
                    )
                ]
            ),
            new OA\Response(response: 401, description: 'Unauthenticated - المستخدم غير مسجل دخول'),
            new OA\Response(response: 403, description: 'Forbidden - لا تملك الصلاحية (ليست رتبة مشرف أو مدير)'),
            new OA\Response(response: 500, description: 'Server error - خطأ داخلي في السيرفر أثناء توليد الـ PDF')
        ]
    )]
    public function exportPdfDocumentation() {}

    #[OA\Get(
        path: '/dashboard/teacher/bootstrap',
        operationId: 'getTeacherBootstrap',
        tags: ['Dashboard'],
        summary: 'تحميل جميع بيانات المعلم اللازمة للعمل Offline',
        description: 'يعيد بيانات الحلقة، الطلاب، الأعذار المعلقة، وإحصائيات الداشبورد في طلب واحد لتقليل عدد الاتصالات ودعم Offline First.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم تحميل بيانات التهيئة بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحميل بيانات التهيئة بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'halaqat',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: 'حلقة التميز')
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: 'rosters',
                                    type: 'object',
                                    example: [
                                        '1' => [
                                            ['id' => 10, 'name' => 'أحمد الحارثي'],
                                            ['id' => 11, 'name' => 'محمد الكندي']
                                        ]
                                    ]
                                ),
                                new OA\Property(property: 'pending_excuses', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(
                                    property: 'dashboard',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'has_halaqa', type: 'boolean', example: true),
                                        new OA\Property(property: 'halaqa_name', type: 'string', example: 'حلقة التميز'),
                                        new OA\Property(
                                            property: 'cards',
                                            type: 'object',
                                            properties: [
                                                new OA\Property(property: 'total_students', type: 'integer', example: 15),
                                                new OA\Property(property: 'evaluated_today', type: 'string', example: '3 / 15'),
                                                new OA\Property(property: 'attendance_percentage', type: 'string', example: '87%'),
                                                new OA\Property(property: 'month_ayahs_progress', type: 'string', example: '250 آية تم تسميعها'),
                                            ]
                                        )
                                    ]
                                )
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden')
        ]
    )]
    public function getTeacherBootstrapDocumentation() {}

    #[OA\Get(
        path: '/dashboard/teacher/dashboard',
        operationId: 'getTeacherDashboardStats',
        tags: ['Dashboard'],
        summary: 'إحصائيات لوحة التحكم الخاصة بالمعلم لحلقته الموكلة',
        description: 'يعيد تفاصيل حلقة المعلم، الكروت الإحصائية لليوم، التنبيهات الخاصة بالطلاب الأكثر غياباً هذا الشهر، وجدولاً بآخر التسميعات والتقييمات المنجزة مؤخراً.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب إحصائيات لوحة تحكم المعلم بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب إحصائيات لوحة تحكم المعلم بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'has_halaqa', type: 'boolean', example: true),
                                new OA\Property(property: 'halaqa_name', type: 'string', example: 'حلقة زيد بن ثابت'),
                                new OA\Property(
                                    property: 'cards',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'total_students', type: 'integer', example: 15),
                                        new OA\Property(property: 'evaluated_today', type: 'string', example: '5 / 15'),
                                        new OA\Property(property: 'attendance_percentage', type: 'string', example: '87%'),
                                        new OA\Property(property: 'month_ayahs_progress', type: 'string', example: '320 آية تم تسميعها')
                                    ]
                                ),
                                new OA\Property(
                                    property: 'alerts',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(
                                            property: 'frequent_absentees',
                                            type: 'array',
                                            items: new OA\Items(
                                                type: 'object',
                                                properties: [
                                                    new OA\Property(property: 'student_name', type: 'string', example: 'عبد الرحمن محمد'),
                                                    new OA\Property(property: 'absent_days', type: 'integer', example: 4)
                                                ]
                                            )
                                        )
                                    ]
                                ),
                                new OA\Property(
                                    property: 'recent_evaluations',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'student_name', type: 'string', example: 'أحمد محمود'),
                                            new OA\Property(property: 'type', type: 'string', example: 'حفظ جديد'),
                                            new OA\Property(property: 'surah', type: 'string', example: 'البقرة'),
                                            new OA\Property(property: 'score', type: 'integer', example: 95),
                                            new OA\Property(property: 'time', type: 'string', example: 'منذ ساعتين')
                                        ]
                                    )
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح له الدخول - Unauthenticated'),
            new OA\Response(response: 403, description: 'ليس لديك صلاحية الوصول (ليست رتبة معلم) - Forbidden'),
            new OA\Response(response: 500, description: 'خطأ داخلي في السيرفر')
        ]
    )]
    public function getTeacherStats() {}

    /**
     * 🔥 تم تحديثها بالكامل هنا لتتوافق مع الـ JSON Response ورابط Supabase
     */
    #[OA\Get(
        path: '/dashboard/teacher/export-pdf',
        operationId: 'downloadTeacherDashboardPdf',
        tags: ['Reports'],
        summary: 'تحميل تقرير لوحة تحكم المعلم PDF',
        description: 'يقوم بإنشاء تقرير PDF مخصص يخص حلقة المعلم الحالي، يرفعه إلى Supabase، ويعيد رابط تحميل موقّع مؤقت مع حالة الكاش لتجنب الضغط على الخادم.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: false,
                description: 'نوع الرد المطلوب',
                schema: new OA\Schema(type: 'string', default: 'application/json')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم إنشاء التقرير بنجاح وعاد برابط تحميل الملف السحابي الموقّع',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء تقرير المعلم بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'url',
                                    type: 'string',
                                    format: 'url',
                                    example: 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/sign/reports/teacher-reports/4/general_1781780709.pdf?token=...'
                                ),
                                new OA\Property(property: 'cached', type: 'boolean', example: false)
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح - يجب تسجيل الدخول أولاً'),
            new OA\Response(response: 403, description: 'ليس لديك صلاحية الوصول (يجب أن تكون معلماً)'),
            new OA\Response(response: 500, description: 'حدث خطأ في الخادم أو أثناء توليد ورفع ملف PDF')
        ]
    )]
    public function exportPdfDoc() {}

    #[OA\Get(
        path: '/dashboard/parent/dashboard',
        operationId: 'getParentDashboardStats',
        tags: ['Dashboard'],
        summary: 'إحصائيات لوحة التحكم الخاصة بولي الأمر (الأب)',
        description: 'يعيد مصفوفة بأسماء كافة الأبناء التابعين لولي الأمر الحالي، مع تفاصيل الحلقة الموكلين بها، وحالة حضورهم اليوم، بالإضافة لآخر تقييم وتسميع حصلوا عليه مع المجموع التراكمي لآيات الحفظ والمراجعة هذا الشهر.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب بيانات لوحة تحكم ولي الأمر بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب بيانات لوحة تحكم ولي الأمر بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'has_children', type: 'boolean', example: true),
                                new OA\Property(
                                    property: 'children',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 12),
                                            new OA\Property(property: 'name', type: 'string', example: 'عمر أحمد محمد'),
                                            new OA\Property(property: 'halaqa', type: 'string', example: 'حلقة عاصم بن أبي النجود'),
                                            new OA\Property(property: 'today_attendance', type: 'string', example: 'present'),
                                            new OA\Property(property: 'month_progress', type: 'string', example: '145 آية المجموع التراكمي'),
                                            new OA\Property(
                                                property: 'last_evaluation',
                                                type: 'object',
                                                nullable: true,
                                                properties: [
                                                    new OA\Property(property: 'surah', type: 'string', example: 'الكهف'),
                                                    new OA\Property(property: 'type', type: 'string', example: 'مراجعة ثانية'),
                                                    new OA\Property(property: 'score', type: 'integer', example: 98),
                                                    new OA\Property(property: 'date', type: 'string', example: 'منذ 4 ساعات')
                                                ]
                                            )
                                        ]
                                    )
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح له الدخول - Unauthenticated'),
            new OA\Response(response: 403, description: 'ليس لديك صلاحية الوصول (ليست رتبة ولي أمر) - Forbidden'),
            new OA\Response(response: 500, description: 'خطأ داخلي في السيرفر')
        ]
    )]
    public function getParentStats() {}

    #[OA\Get(
        path: '/dashboard/parent/export-pdf',
        operationId: 'exportParentDashboardPdf',
        tags: ['Reports'],
        summary: 'إنشاء رابط تحميل تقرير ولي الأمر PDF',
        description: 'يقوم بإنشاء تقرير PDF متعدد الصفحات يحتوي على متابعة الأبناء، ثم يرفعه إلى Supabase ويعود برابط موقّع مؤقت للتحميل.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم إنشاء التقرير بنجاح وعاد بالرابط الموقّع',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء التقرير بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'url',
                                    type: 'string',
                                    format: 'uri',
                                    example: 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/sign/reports/parent-reports/5/1780904552.pdf?token=...'
                                ),
                                new OA\Property(property: 'cached', type: 'boolean', example: false)
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح - يجب تسجيل الدخول'),
            new OA\Response(response: 403, description: 'ليس لديك صلاحية للوصول (يجب أن تكون ولي أمر)'),
            new OA\Response(response: 404, description: 'لا يوجد أبناء مرتبطون بهذا الحساب حالياً'),
            new OA\Response(response: 500, description: 'حدث خطأ في الخادم أو أثناء توليد ورفع ملف PDF')
        ]
    )]
    public function exportPdf() {}

    #[OA\Get(
        path: '/dashboard/mosque-manager',
        operationId: 'getMosqueManagerDashboard',
        tags: ['Dashboard'],
        summary: 'عرض بيانات لوحة تحكم مدير المسجد بالكامل',
        description: 'تُرجع المؤشرات الرئيسية، الرسم البياني للحضور، أحدث النشاطات، مهام اليوم، وجدول الشكاوى.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dashboard data retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب بيانات لوحة التحكم بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'kpi_cards', type: 'object'),
                                new OA\Property(property: 'attendance_chart', type: 'object'),
                                new OA\Property(property: 'recent_activities', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'today_tasks', type: 'object'),
                                new OA\Property(property: 'latest_tickets', type: 'array', items: new OA\Items(type: 'object')),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function getMosqueManagerDashboard()
    {
    }

    #[OA\Get(
        path: '/dashboard/mosque-manager/statistics',
        operationId: 'getMosqueManagerStatistics',
        tags: ['Dashboard'],
        summary: 'إحصائيات المسجد لمدير المسجد',
        description: 'يعيد الإحصائيات الأساسية الخاصة بالمسجد الذي ينتمي إليه مدير المسجد، وتشمل إجمالي طلاب الحلقات، المعلمين والمقرئين، المتطوعين، ودعوات التسجيل المعلقة التي لم يتم قبولها ولم تنتهِ صلاحيتها.',
        security: [['bearerAuth' => []]],

        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب إحصائيات المسجد بنجاح',
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
                            example: 'تم جلب إحصائيات المسجد بنجاح'
                        ),

                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [

                                new OA\Property(
                                    property: 'total_students',
                                    type: 'integer',
                                    example: 120,
                                    description: 'إجمالي الطلاب المسجلين في حلقات المسجد'
                                ),

                                new OA\Property(
                                    property: 'total_teachers',
                                    type: 'integer',
                                    example: 15,
                                    description: 'إجمالي المعلمين والمقرئين في المسجد'
                                ),

                                new OA\Property(
                                    property: 'total_volunteers',
                                    type: 'integer',
                                    example: 8,
                                    description: 'إجمالي المتطوعين المسجلين في المسجد'
                                ),

                                new OA\Property(
                                    property: 'pending_invitations',
                                    type: 'integer',
                                    example: 3,
                                    description: 'إجمالي دعوات التسجيل المعلقة وغير منتهية الصلاحية'
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
                response: 401,
                description: 'Unauthenticated - المستخدم غير مسجل الدخول'
            ),

            new OA\Response(
                response: 403,
                description: 'Forbidden - ليس لديك صلاحية الوصول إلى إحصائيات المسجد'
            ),

            new OA\Response(
                response: 500,
                description: 'Server error - خطأ داخلي في السيرفر'
            )
        ]
    )]
    public function getMosqueManagerStatistics() {}

    #[OA\Get(
        path: '/dashboard/mosque-manager/export-pdf',
        operationId: 'exportMosqueManagerDashboardPdf',
        tags: ['Reports'],
        summary: 'تصدير تقرير مدير المسجد PDF',
        description: 'يولّد تقرير PDF بلوحة تحكم مدير المسجد (المؤشرات، الحضور، الأنشطة، البلاغات، الإحصائيات) ويرفعه إلى Supabase ويعيد رابطاً موقّعاً مؤقتاً مع حالة الكاش.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم إنشاء التقرير بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء تقرير مدير المسجد بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'url', type: 'string', format: 'url', example: 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/sign/reports/mosque-manager-reports/1_user_3_...pdf?token=...'),
                                new OA\Property(property: 'cached', type: 'boolean', example: false)
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden')
        ]
    )]
    public function exportMosqueManagerPdf() {}

    #[OA\Get(
        path: '/admin/dashboard',
        operationId: 'getAdminDashboardStats',
        tags: ['Dashboard'],
        summary: 'إحصائيات لوحة تحكم مدير المنطقة (النظام ككل)',
        description: 'يعيد مؤشرات النظام الإجمالية: عدد المساجد، الطلاب، الحلقات، المستخدمين حسب الدور، التبرعات، الشكاوى وطلبات الصيانة.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب إحصائيات لوحة تحكم المدير بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب إحصائيات لوحة تحكم المدير بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'totals',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'mosques', type: 'integer', example: 12),
                                        new OA\Property(property: 'students', type: 'integer', example: 340),
                                        new OA\Property(property: 'halaqas', type: 'integer', example: 28),
                                        new OA\Property(property: 'teachers', type: 'integer', example: 30),
                                        new OA\Property(property: 'volunteers', type: 'integer', example: 45),
                                        new OA\Property(property: 'managers', type: 'integer', example: 10),
                                        new OA\Property(property: 'supervisors', type: 'integer', example: 14),
                                        new OA\Property(property: 'parents', type: 'integer', example: 250)
                                    ]
                                ),
                                new OA\Property(property: 'donations', type: 'number', format: 'float', example: 125000.0),
                                new OA\Property(
                                    property: 'complaints',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'total', type: 'integer', example: 40),
                                        new OA\Property(property: 'pending', type: 'integer', example: 7)
                                    ]
                                ),
                                new OA\Property(
                                    property: 'maintenance',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'total', type: 'integer', example: 22),
                                        new OA\Property(property: 'pending', type: 'integer', example: 5)
                                    ]
                                )
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — super_admin only')
        ]
    )]
    public function getAdminStats() {}

    #[OA\Get(
        path: '/admin/export-pdf',
        operationId: 'exportAdminDashboardPdf',
        tags: ['Reports'],
        summary: 'تصدير تقرير مدير المنطقة PDF',
        description: 'يولّد تقرير PDF ملخّص النظام ككل ويرفعه إلى Supabase ويعيد رابطاً موقّعاً مؤقتاً.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم إنشاء التقرير بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء تقرير المدير بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'url', type: 'string', format: 'url'),
                                new OA\Property(property: 'cached', type: 'boolean', example: false)
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — super_admin only')
        ]
    )]
    public function exportAdminPdf() {}

    #[OA\Get(
        path: '/reports/mosques/census',
        operationId: 'exportMosqueCensusPdf',
        tags: ['Reports'],
        summary: 'التقرير الإحصائي الشامل لمساجد المنطقة',
        description: 'تقرير PDF لكافة مساجد المنطقة مصنّفة حسب المدينة، مع الحالة التشغيلية والسعة والمرافق والموقع الجغرافي. مخصص لمدير المنطقة (super_admin).',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم إنشاء التقرير الإحصائي الشامل بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء التقرير الإحصائي الشامل للمساجد بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'url', type: 'string', format: 'url'),
                                new OA\Property(property: 'cached', type: 'boolean', example: false)
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — super_admin only')
        ]
    )]
    public function exportMosqueCensusPdf() {}

    #[OA\Get(
        path: '/reports/mosques/readiness',
        operationId: 'exportMosqueReadinessPdf',
        tags: ['Reports'],
        summary: 'تقرير جاهزية المساجد للمواسم',
        description: 'تقرير PDF لجاهزية الجوامع الكبرى للمواسم (رمضان، التراويح، العيدين، الجمعة) بناءً على المرافق وطلبات الصيانة المفتوحة. مخصص لمدير المنطقة (super_admin).',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم إنشاء تقرير الجاهزية بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء تقرير الجاهزية للمواسم بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'url', type: 'string', format: 'url'),
                                new OA\Property(property: 'cached', type: 'boolean', example: false)
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — super_admin only')
        ]
    )]
    public function exportMosqueReadinessPdf() {}

    #[OA\Get(
        path: '/reports/mosques/fact-sheet',
        operationId: 'exportMosqueFactSheetPdf',
        tags: ['Reports'],
        summary: 'تقرير بطاقة المسجد التعريفية',
        description: 'تقرير PDF لملف المسجد الكامل (العنوان، الموقع GPS، المدير، الإمام، الخطيب، القاعات، المرافق، الاحتياجات). مدير المسجد مقصور على مسجده؛ مدير المنطقة يمرر mosque_id.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'mosque_id',
                in: 'query',
                required: false,
                description: 'معرف المسجد (إلزامي لـ super_admin لتحديد المسجد؛ يتجاهله مدير المسجد ويُقصر على مسجده)',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم إنشاء بطاقة المسجد التعريفية بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء بطاقة المسجد التعريفية بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'url', type: 'string', format: 'url'),
                                new OA\Property(property: 'cached', type: 'boolean', example: false)
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Mosque not found')
        ]
    )]
    public function exportMosqueFactSheetPdf() {}
}
