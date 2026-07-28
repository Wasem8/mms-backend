<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class GeoEndpoints
{
    #[OA\Get(
        path: '/geo',
        operationId: 'getGeoCatalog',
        tags: ['Geo'],
        summary: 'جلب الكاتالوج الجغرافي',
        description: 'يرجع شجرة جغرافية (محافظة ← مدينة ← حي) لسوريا. تُستخدم لاختيار الموقع بالتطبيق ولفلترة المساجد. الأسماء تُرجع مترجمة حسب Accept-Language. لا يتطلب توكن.',
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                description: 'لغة الأسماء المرجعة (ar أو en)',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['ar', 'en'], default: 'ar')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'شجرة الكاتالوج الجغرافي',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب الكاتالوج الجغرافي بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'دمشق'),
                                    new OA\Property(property: 'lat', type: 'number', format: 'float', example: 33.5138),
                                    new OA\Property(property: 'lng', type: 'number', format: 'float', example: 36.2765),
                                    new OA\Property(
                                        property: 'cities',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'integer', example: 101),
                                                new OA\Property(property: 'name', type: 'string', example: 'دوما'),
                                                new OA\Property(property: 'lat', type: 'number', format: 'float', example: 33.5722),
                                                new OA\Property(property: 'lng', type: 'number', format: 'float', example: 36.4022),
                                                new OA\Property(
                                                    property: 'districts',
                                                    type: 'array',
                                                    items: new OA\Items(
                                                        properties: [
                                                            new OA\Property(property: 'id', type: 'integer', example: 1001),
                                                            new OA\Property(property: 'name', type: 'string', example: 'المزة'),
                                                            new OA\Property(property: 'lat', type: 'number', format: 'float', nullable: true, example: 33.5024),
                                                            new OA\Property(property: 'lng', type: 'number', format: 'float', nullable: true, example: 36.2380),
                                                        ]
                                                    )
                                                ),
                                            ]
                                        )
                                    ),
                                ]
                            )
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
        ]
    )]
    public function index() {}
}