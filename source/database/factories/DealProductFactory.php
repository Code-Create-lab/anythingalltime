<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DealProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealProductFactory extends Factory
{
    protected $model = DealProduct::class;

    public function definition(): array
    {
        return [
            'varient_id' => 1,
            'store_id' => 1,
            'deal_price' => $this->faker->randomFloat(2, 30, 100),
            'valid_from' => Carbon::now()->subDay(),
            'valid_to' => Carbon::now()->addDay(),
            'status' => 1,
        ];
    }
}
