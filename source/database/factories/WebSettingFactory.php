<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WebSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebSettingFactory extends Factory
{
    protected $model = WebSetting::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'icon' => $this->faker->imageUrl(100, 100, 'business'),
            'favicon' => $this->faker->imageUrl(32, 32, 'business'),
            'live_chat' => 1,
            'number_limit' => 10,
            'last_loc' => $this->faker->city,
        ];
    }
}
