<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrdersFactory extends Factory
{
    protected $model = Orders::class;

    public function definition(): array
    {
        return [
            'order_id' => $this->faker->unique()->numberBetween(1, 1000),
            'cart_id' => $this->faker->regexify('[A-Z]{4}[0-9]{2}[a-z]{2}'),
            'total_price' => $this->faker->randomFloat(2, 100, 1000),
            'price_without_delivery' => $this->faker->randomFloat(2, 80, 800),
            'total_products_mrp' => $this->faker->randomFloat(2, 120, 1200),
            'delivery_charge' => $this->faker->randomFloat(2, 0, 100),
            'user_id' => 1,
            'store_id' => 1,
            'rem_price' => $this->faker->randomFloat(2, 100, 1000),
            'order_date' => Carbon::now(),
            'delivery_date' => Carbon::now()->addDay(),
            'time_slot' => '10:00-12:00',
            'address_id' => 1,
            'avg_tax_per' => $this->faker->randomFloat(2, 5, 20),
            'total_tax_price' => $this->faker->randomFloat(2, 10, 100),
            'payment_method' => 'COD',
            'order_status' => 'pending',
        ];
    }
}
