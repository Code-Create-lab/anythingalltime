<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FreeDeliveryCart;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreeDeliveryCartFactory extends Factory
{
    protected $model = FreeDeliveryCart::class;

    public function definition(): array
    {
        return [
            'store_id' => 1,
            'min_cart_value' => $this->faker->numberBetween(200, 1000),
            'del_charge' => $this->faker->numberBetween(10, 100),
        ];
    }
}
