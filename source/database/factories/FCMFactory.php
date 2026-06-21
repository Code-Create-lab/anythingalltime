<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FCM;
use Illuminate\Database\Eloquent\Factories\Factory;

class FCMFactory extends Factory
{
    protected $model = FCM::class;

    public function definition(): array
    {
        return [
            'server_key' => $this->faker->sha256,
            'store_server_key' => $this->faker->sha256,
            'driver_server_key' => $this->faker->sha256,
        ];
    }
}
