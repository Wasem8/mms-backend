<?php

namespace Modules\Education\Enums;

enum EvaluationLabel: string
{
    case EXCELLENT = 'excellent';
    case VERY_GOOD = 'very_good';
    case GOOD = 'good';
    case NEEDS_WORK = 'needs_work';

    public function label(): string
    {
        return match ($this) {
            self::EXCELLENT  => __('ممتاز'),
            self::VERY_GOOD  => __('جيد جداً'),
            self::GOOD       => __('جيد'),
            self::NEEDS_WORK => __('يحتاج تحسين'),
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'key'  => $case->value,
            'name' => $case->label(),
        ], self::cases());
    }
}
