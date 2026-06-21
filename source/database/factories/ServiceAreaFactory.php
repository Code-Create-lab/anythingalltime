<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceAreaFactory extends Factory
{
    protected $model = ServiceArea::class;

    public function definition(): array
    {
        return [
            'area_name' => $this->faker->city,
            'society_name' => $this->faker->city,
            'society_id' => $this->faker->numberBetween(1, 100),
            'delivery_charge' => $this->faker->numberBetween(0, 100),
            'store_id' => 1,
            'added_by' => 1,
            'enabled' => 1,
            'city_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
