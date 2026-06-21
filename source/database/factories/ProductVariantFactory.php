<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => 1,
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit' => $this->faker->randomElement(['kg', 'g', 'l', 'ml', 'pcs']),
            'base_mrp' => $this->faker->randomFloat(2, 50, 200),
            'base_price' => $this->faker->randomFloat(2, 40, 150),
            'description' => $this->faker->sentence,
            'varient_image' => $this->faker->imageUrl(),
            'ean' => $this->faker->ean13,
            'approved' => 1,
            'added_by' => 1,
        ];
    }
}
