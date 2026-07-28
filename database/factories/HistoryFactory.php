<?php

namespace Database\Factories;

use App\Enum\NotificationStatus;
use App\Models\History;
use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoryFactory extends Factory
{
    protected $model = History::class;

    public function definition()
    {
        return [
            'user_id' => Users::factory(),
            'channel' => $this->faker->randomElement(['email', 'telegram']),
            'status' => NotificationStatus::Processing,
            'message' => $this->faker->sentence,
            'destination' => $this->faker->email,
            'attempts' => 0,
            'sent_at' => null,
            'error_message' => null,
        ];
    }
}
