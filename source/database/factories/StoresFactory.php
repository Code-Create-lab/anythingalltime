<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Stores;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoresFactory extends Factory
{
    protected $model = Stores::class;

    public function definition(): array
    {
        return [
            'store_name' => $this->faker->company,
            'employee_name' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber,
            'store_photo' => 'N/A',
            'city' => $this->faker->city,
            'city_id' => $this->faker->numberBetween(1, 10),
            'admin_share' => $this->faker->randomFloat(2, 0, 100),
            'device_id' => $this->faker->uuid,
            'email' => $this->faker->email,
            'password' => bcrypt('password'),
            'del_range' => $this->faker->randomFloat(2, 5, 20),
            'lat' => (string) $this->faker->latitude,
            'lng' => (string) $this->faker->longitude,
            'address' => $this->faker->address,
            'admin_approval' => 1,
            'orders' => 1,
            'store_status' => 1,
            'store_opening_time' => '09:00',
            'store_closing_time' => '18:00',
            'time_interval' => 30,
            'inactive_reason' => null,
            'id_type' => null,
            'id_number' => null,
            'id_photo' => null,
        ];
    }
}
