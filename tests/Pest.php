<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Modules/Education/tests/Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function something()
{
    // ..
}
