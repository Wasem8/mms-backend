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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['halaqa_id', 'student_id'],
                properties: [
                    new OA\Property(property: 'halaqa_id', type: 'integer', example: 1),
                    new OA\Property(property: 'student_id', type: 'integer', example: 10),
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
}
