<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cities;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitiesFactory extends Factory
{
    protected $model = Cities::class;

    public function definition(): array
    {
        return [
            'city_id' => $this->faker->unique()->numberBetween(1, 9999),
            'city_name' => $this->faker->city,
        ];
    }
}
