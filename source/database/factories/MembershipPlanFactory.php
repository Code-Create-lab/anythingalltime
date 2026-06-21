<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipPlanFactory extends Factory
{
    protected $model = MembershipPlan::class;

    public function definition(): array
    {
        return [
            'plan_name' => $this->faker->word,
            'price' => $this->faker->numberBetween(100, 1000),
            'days' => $this->faker->numberBetween(30, 365),
            'free_delivery' => $this->faker->randomElement([0, 1]),
            'instant_delivery' => $this->faker->randomElement([0, 1]),
            'reward' => $this->faker->numberBetween(0, 100),
            'plan_description' => $this->faker->sentence,
            'hide' => 0,
        ];
    }
}
