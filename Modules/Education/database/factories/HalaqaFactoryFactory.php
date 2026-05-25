<?php

namespace Modules\Education\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Education\Models\Halaqa;
use Modules\User\Models\User;
use Modules\Mosque\Models\Mosque;

class HalaqaFactory extends Factory
{
    protected $model = Halaqa::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' حلقة',
            'teacher_id' => null,
            'capacity' => $this->faker->numberBetween(10, 50),
            'mosque_id' => null,
            'level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'schedule_days' => ['sunday', 'tuesday', 'thursday'],
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'active',
        ];
    }

    public function forMosque(Mosque $mosque): self
    {
        return $this->state(fn (array $attributes) => [
            'mosque_id' => $mosque->id,
        ]);
    }

    public function withTeacher(User $teacher): self
    {
        return $this->state(fn (array $attributes) => [
            'teacher_id' => $teacher->id,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
