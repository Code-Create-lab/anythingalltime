<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MinimumMaximumOrderValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class MinimumMaximumOrderValueFactory extends Factory
{
    protected $model = MinimumMaximumOrderValue::class;

    public function definition(): array
    {
        return [
            'store_id' => 1,
            'min_value' => (string) $this->faker->randomFloat(2, 50, 200),
            'max_value' => (string) $this->faker->randomFloat(2, 500, 1000),
        ];
    }
}
