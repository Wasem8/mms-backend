<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class DashboardEndpoints
{
    #[OA\Get(
        path: '/dashboard/supervisor/stats',
        operationId: 'getSupervisorDashboardStats',
        tags: ['Dashboard'],
        summary: 'إحصائيات لوحة التحكم للمشرف',
        description: 'يعيد أرقام البطاقات (الطلاب، المعلمين، الحلقات)، الرسم البياني للحضور الأسبوعي، وأحدث النشاطات.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم جلب البيانات بنجاح',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب إحصائيات لوحة التحكم بنجاح'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SupervisorDashboardResponse'), // هنا الربط
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح له الدخول'),
            new OA\Response(response: 403, description: 'ليس لديك صلاحية الوصول'),
            new OA\Response(response: 500, description: 'خطأ في السيرفر')
        ]
    )]
    public function getSupervisorStats()
    {
        // هذه الدالة تبقى فارغة هنا، هي فقط للتوثيق
    }
}
