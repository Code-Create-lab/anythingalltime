<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'user_phone' => $this->faker->phoneNumber(),
            'device_id' => $this->faker->uuid(),
            'user_image' => 'N/A',
            'user_city' => 1,
            'user_area' => 1,
            'otp_value' => null,
            'status' => 1,
            'wallet' => $this->faker->randomFloat(2, 0, 1000),
            'rewards' => $this->faker->numberBetween(0, 100),
            'is_verified' => 1,
            'block' => 2,
            'reg_date' => $this->faker->date(),
            'app_update' => 1,
            'facebook_id' => null,
            'referral_code' => $this->faker->regexify('[A-Z]{6}'),
            'membership' => 0,
            'mem_plan_start' => null,
            'mem_plan_expiry' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
