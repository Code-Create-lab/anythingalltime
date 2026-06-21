<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Firebase;
use Illuminate\Database\Eloquent\Factories\Factory;

class FirebaseFactory extends Factory
{
    protected $model = Firebase::class;

    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(['0', '1']),
        ];
    }
}
