<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StoreBanner;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreBannerFactory extends Factory
{
    protected $model = StoreBanner::class;

    public function definition(): array
    {
        return [
            'store_id' => 1,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'image_url' => $this->faker->imageUrl(400, 200, 'business'),
        ];
    }
}
