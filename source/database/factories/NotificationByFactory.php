<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationBy;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationByFactory extends Factory
{
    protected $model = NotificationBy::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'sms' => 1,
            'email' => 1,
            'app' => 1,
        ];
    }
}
