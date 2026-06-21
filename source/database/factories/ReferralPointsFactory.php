<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralPoints;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralPointsFactory extends Factory
{
    protected $model = ReferralPoints::class;

    public function definition(): array
    {
        return [
            'points' => json_encode(['min' => 10, 'max' => 100]),
        ];
    }
}
