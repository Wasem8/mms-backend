<?php

namespace App\OpenApi\Shared;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProgramSchedule',
    description: 'A single session schedule belonging to a Dawah program',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'dawah_program_id', type: 'integer', example: 3),
        new OA\Property(property: 'title', type: 'string', nullable: true, example: 'الجلسة الأولى - مقدمة التجويد'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'يرجى إحضار المصحف الشريف'),
        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-06-15'),
        new OA\Property(property: 'start_time', type: 'string', example: '10:00'),
        new OA\Property(property: 'end_time', type: 'string', example: '11:30'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-01T08:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-10T12:00:00Z'),
    ]
)]
#[OA\Schema(
    schema: 'DawahProgramWithSchedules',
    description: 'A Dawah program with all its session schedules',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 3),
        new OA\Property(property: 'mosque_id', type: 'integer', example: 1),
        new OA\Property(property: 'space_id', type: 'integer', example: 2),
        new OA\Property(property: 'program_name', type: 'string', example: 'درس القرآن الكريم'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'درس أسبوعي لتعليم القرآن الكريم وأحكام التجويد'),
        new OA\Property(property: 'type', type: 'string', enum: ['lecture', 'course', 'compition', 'other'], example: 'course'),
        new OA\Property(property: 'image', type: 'string', nullable: true, example: 'https://your-supabase-url.co/storage/v1/object/public/bucket/abc123.jpg'),
        new OA\Property(property: 'presenter', type: 'string', example: 'الشيخ أحمد بن محمد'),
        new OA\Property(property: 'presenter_image', type: 'string', nullable: true, example: 'https://your-supabase-url.co/storage/v1/object/public/bucket/presenter.jpg'),
        new OA\Property(property: 'is_featured', type: 'boolean', example: false),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'level', type: 'string', enum: ['beginner', 'intermediate', 'advanced'], example: 'beginner'),
        new OA\Property(
            property: 'schedules',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProgramSchedule')
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-01T08:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-10T12:00:00Z'),
    ]
)]
class DawahProgramSchemas {}
