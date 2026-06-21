<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word,
            'slug' => $this->faker->slug,
            'url' => $this->faker->url,
            'image' => $this->faker->imageUrl(),
            'parent' => 0,
            'level' => 1,
            'description' => $this->faker->sentence,
            'status' => 1,
            'added_by' => 1,
            'tax_type' => $this->faker->randomElement([1, 2]),
            'tax_name' => 'GST',
            'tax_per' => $this->faker->randomFloat(2, 5, 20),
            'tx_id' => null,
        ];
    }
}
