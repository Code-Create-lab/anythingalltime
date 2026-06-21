<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'coupon_id' => $this->faker->unique()->numberBetween(1, 9999),
            'store_id' => 1,
            'coupon_name' => $this->faker->words(2, true),
            'coupon_image' => $this->faker->imageUrl(200, 200, 'business'),
            'coupon_code' => $this->faker->regexify('[A-Z]{6}[0-9]{4}'),
            'coupon_description' => $this->faker->sentence,
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
            'cart_value' => $this->faker->numberBetween(10, 1000),
            'amount' => $this->faker->numberBetween(5, 100),
            'type' => $this->faker->randomElement(['percent', 'amount']),
            'uses_restriction' => $this->faker->numberBetween(1, 10),
        ];
    }
}
