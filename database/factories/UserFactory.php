<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Models\User;
use Modules\Mosque\Models\Mosque;

class UserFactory extends Factory
{
    protected $model = User::class;
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => 'active',
            'mosque_id' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function supervisor(Mosque $mosque = null): self
    {
        return $this->state(fn (array $attributes) => [
            'mosque_id' => $mosque?->id ?? Mosque::factory(),
        ])->afterCreating(function (User $user) {
            $user->assignRole('halaqa_supervisor');
        });
    }

    public function teacher(Mosque $mosque = null): self
    {
        return $this->state(fn (array $attributes) => [
            'mosque_id' => $mosque?->id ?? Mosque::factory(),
        ])->afterCreating(function (User $user) {
            $user->assignRole('teacher');
        });
    }

    public function areaManager(): self
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('super_admin');
        });
    }

    public function parent(): self
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('parent');
        });
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
