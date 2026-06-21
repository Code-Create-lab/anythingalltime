<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Town;
use Illuminate\Database\Eloquent\Factories\Factory;

class TownFactory extends Factory
{
    protected $model = Town::class;

    public function definition(): array
    {
        return [
            'society_id' => $this->faker->unique()->numberBetween(1, 9999),
            'society_name' => $this->faker->streetName,
            'city_id' => $this->faker->numberBetween(1, 9999),
        ];
    }
}
