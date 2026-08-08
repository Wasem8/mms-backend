<?php

namespace Modules\Mosque\Enums;

enum MosqueTaskCategory: string
{
case PrayerWorship = 'prayer_worship';
case Cleaning = 'cleaning';
case Maintenance = 'maintenance';
case Activity = 'activity';
case Administrative = 'administrative';

public function label(): string
{
return match ($this) {
self::PrayerWorship => __('messages.mosque_task_category_prayer_worship'),
self::Cleaning => __('messages.mosque_task_category_cleaning'),
self::Maintenance => __('messages.mosque_task_category_maintenance'),
self::Activity => __('messages.mosque_task_category_activity'),
self::Administrative => __('messages.mosque_task_category_administrative'),
};
}
}
