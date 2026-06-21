<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeliveryBoy;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryBoyFactory extends Factory
{
    protected $model = DeliveryBoy::class;

    public function definition(): array
    {
        return [
            'boy_name' => $this->faker->name,
            'boy_phone' => $this->faker->phoneNumber,
            'boy_city' => $this->faker->numberBetween(1, 10),
            'boy_address' => $this->faker->address,
            'password' => 'password123', // Plain text as per existing controller
            'device_id' => $this->faker->uuid,
            'boy_loc' => $this->faker->address,
            'lat' => (string) $this->faker->latitude,
            'lng' => (string) $this->faker->longitude,
            'status' => 1,
            'store_id' => 0,
            'store_dboy_id' => 0,
            'added_by' => 'admin',
            'image' => 'default.jpg',
        ];
    }

    /**
     * Indicate that the delivery boy is inactive.
     */
    public function inactive(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 0,
            ];
        });
    }

    /**
     * Indicate that the delivery boy is online.
     */
    public function online(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 1,
            ];
        });
    }

    /**
     * Indicate that the delivery boy is added by store.
     */
    public function addedByStore(int $storeId): Factory
    {
        return $this->state(function (array $attributes) use ($storeId) {
            return [
                'store_id' => $storeId,
                'added_by' => 'store',
            ];
        });
    }

    /**
     * Indicate that the delivery boy is assigned to store.
     */
    public function assignedToStore(int $storeId, int $storeDboyId): Factory
    {
        return $this->state(function (array $attributes) use ($storeId, $storeDboyId) {
            return [
                'store_id' => $storeId,
                'store_dboy_id' => $storeDboyId,
            ];
        });
    }
}
