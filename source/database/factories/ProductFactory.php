<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'cat_id' => 1,
            'product_name' => $this->faker->word,
            'product_image' => $this->faker->imageUrl(),
            'type' => 'Regular',
            'hide' => 0,
            'added_by' => 1,
            'approved' => 1,
        ];
    }
}
