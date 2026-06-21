<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'store_name' => $this->faker->company,
            'employee_name' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber,
            'store_photo' => 'default.jpg',
            'city' => $this->faker->city,
            'city_id' => $this->faker->numberBetween(1, 10),
            'admin_share' => $this->faker->randomFloat(2, 5, 20),
            'device_id' => $this->faker->uuid,
            'email' => $this->faker->unique()->safeEmail,
            'password' => \Hash::make('password123'),
            'del_range' => $this->faker->randomFloat(2, 5, 20),
            'lat' => (string) $this->faker->latitude,
            'lng' => (string) $this->faker->longitude,
            'address' => $this->faker->address,
            'admin_approval' => 1,
            'orders' => 1,
            'store_status' => 1,
            'store_opening_time' => '09:00',
            'store_closing_time' => '21:00',
            'time_interval' => 30,
            'inactive_reason' => null,
            'id_type' => null,
            'id_number' => null,
            'id_photo' => null,
        ];
    }

    /**
     * Indicate that the store is not approved.
     */
    public function unapproved(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'admin_approval' => 0,
            ];
        });
    }

    /**
     * Indicate that the store is blocked.
     */
    public function blocked(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'store_status' => 0,
                'inactive_reason' => 'Blocked by admin',
            ];
        });
    }

    /**
     * Indicate that the store has specific working hours.
     */
    public function withWorkingHours(string $opening, string $closing): Factory
    {
        return $this->state(function (array $attributes) use ($opening, $closing) {
            return [
                'store_opening_time' => $opening,
                'store_closing_time' => $closing,
            ];
        });
    }
}
