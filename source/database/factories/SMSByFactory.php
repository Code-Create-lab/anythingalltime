<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SMSBy;
use Illuminate\Database\Eloquent\Factories\Factory;

class SMSByFactory extends Factory
{
    protected $model = SMSBy::class;

    public function definition(): array
    {
        return [
            'status' => 1,
        ];
    }
}
