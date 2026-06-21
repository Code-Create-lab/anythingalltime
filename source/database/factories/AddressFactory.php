<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'receiver_name' => $this->faker->name,
            'receiver_phone' => $this->faker->phoneNumber,
            'society' => $this->faker->city,
            'city' => $this->faker->city,
            'city_id' => 1,
            'society_id' => 1,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'select_status' => 1,
            'type' => 'Home',
            'house_no' => $this->faker->buildingNumber,
            'state' => $this->faker->state,
            'pincode' => $this->faker->postcode,
            'landmark' => $this->faker->secondaryAddress,
            'added_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
