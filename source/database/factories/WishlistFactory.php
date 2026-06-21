<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistFactory extends Factory
{
    protected $model = Wishlist::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'varient_id' => 1,
            'quantity' => (string) $this->faker->numberBetween(1, 5),
            'unit' => $this->faker->randomElement(['kg', 'g', 'l', 'ml', 'pcs']),
            'price' => (string) $this->faker->randomFloat(2, 40, 150),
            'mrp' => (string) $this->faker->randomFloat(2, 50, 200),
            'product_name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'varient_image' => $this->faker->imageUrl(),
            'store_id' => 1,
        ];
    }
}
