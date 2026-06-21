<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StoreOrders;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreOrdersFactory extends Factory
{
    protected $model = StoreOrders::class;

    public function definition(): array
    {
        return [
            'store_id' => 1,
            'varient_id' => 1,
            'qty' => $this->faker->numberBetween(1, 5),
            'product_name' => $this->faker->word,
            'varient_image' => $this->faker->imageUrl(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit' => $this->faker->randomElement(['kg', 'g', 'l', 'ml', 'pcs']),
            'store_approval' => 1,
            'total_mrp' => $this->faker->randomFloat(2, 50, 200),
            'order_cart_id' => 'incart',
            'order_date' => now(),
            'price' => $this->faker->randomFloat(2, 40, 150),
            'description' => $this->faker->sentence,
            'tx_per' => $this->faker->randomFloat(2, 5, 20),
            'price_without_tax' => $this->faker->randomFloat(2, 35, 130),
            'tx_price' => $this->faker->randomFloat(2, 2, 15),
            'tx_name' => 'GST',
            'type' => 'Regular',
        ];
    }
}
