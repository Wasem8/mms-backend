<?php

namespace Modules\Education\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Education\Models\Student;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'parent_id' => User::factory(),
            'first_name' => $this->faker->firstName('male'),
            'last_name' => $this->faker->lastName(),
            'mosque_id' => Mosque::factory(),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-5 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'status' => 'active',
        ];
    }

    public function forMosque(Mosque $mosque): self
    {
        return $this->state(fn (array $attributes) => [
            'mosque_id' => $mosque->id,
        ]);
    }

    public function withParent(User $parent): self
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
