<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StoreProducts;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreProductsFactory extends Factory
{
    protected $model = StoreProducts::class;

    public function definition(): array
    {
        return [
            'store_id' => 1,
            'varient_id' => 1,
            'price' => $this->faker->randomFloat(2, 40, 150),
            'mrp' => $this->faker->randomFloat(2, 50, 200),
            'stock' => $this->faker->numberBetween(10, 100),
            'min_ord_qty' => 1,
            'max_ord_qty' => 10,
        ];
    }
}
