<?php

namespace Database\Factories;

use App\Models\NotificationChannels;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationChannelsFactory extends Factory
{
    protected $model = NotificationChannels::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->word,
            'handler' => 'App\Channels\EmailChannel',
            'active' => true,
        ];
    }
}
