<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CountryCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryCodeFactory extends Factory
{
    protected $model = CountryCode::class;

    public function definition(): array
    {
        return [
            'country_code' => '+1',
        ];
    }
}
