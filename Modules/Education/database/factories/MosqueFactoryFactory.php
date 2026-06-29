<?php

namespace Modules\Mosque\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Mosque\Models\Mosque;

class MosqueFactory extends Factory
{
    protected $model = Mosque::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' جامع',
            'city' => $this->faker->city(),
            'district' => $this->faker->word(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'status' => 'active',
            'is_featured' => false,
            'average_rating' => 4.5,
            'reviews_count' => 10,
            'imam' => $this->faker->name(),
            'khatib' => $this->faker->name(),
        ];
    }
}
