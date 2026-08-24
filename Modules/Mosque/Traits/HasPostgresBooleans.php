<?php

namespace Modules\Mosque\Traits;

trait HasPostgresBooleans
{
    protected static function bootHasPostgresBooleans(): void
    {
        static::retrieved(function ($model) {
            $model->fixPostgresBooleans();
        });

        static::saving(function ($model) {
            foreach ($model->getCasts() as $attribute => $type) {
                if ($type === 'boolean' && array_key_exists($attribute, $model->getAttributes())) {
                    $value = $model->getAttributes()[$attribute];
                    $model->attributes[$attribute] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                }
            }
        });

        static::saved(function ($model) {
            $model->fixPostgresBooleans();
        });
    }

    protected function fixPostgresBooleans(): void
    {
        foreach ($this->getCasts() as $attribute => $type) {
            if ($type === 'boolean' && array_key_exists($attribute, $this->attributes)) {
                $this->attributes[$attribute] = filter_var($this->attributes[$attribute], FILTER_VALIDATE_BOOLEAN);
            }
        }
    }
}
