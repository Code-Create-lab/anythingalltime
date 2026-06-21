<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductRating;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductRatingFactory extends Factory
{
    protected $model = ProductRating::class;

    public function definition(): array
    {
        return [
            'varient_id' => 1,
            'store_id' => 1,
            'user_id' => 1,
            'rating' => (string) $this->faker->numberBetween(1, 5),
            'description' => $this->faker->sentence,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
