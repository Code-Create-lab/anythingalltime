<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FirebaseISO;
use Illuminate\Database\Eloquent\Factories\Factory;

class FirebaseISOFactory extends Factory
{
    protected $model = FirebaseISO::class;

    public function definition(): array
    {
        return [
            'iso_code' => 'US',
        ];
    }
}
