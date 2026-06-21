<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AppLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppLinkFactory extends Factory
{
    protected $model = AppLink::class;

    public function definition(): array
    {
        return [
            'android_app_link' => 'https://play.google.com/test',
            'ios_app_link' => 'https://apps.apple.com/test',
        ];
    }
}
