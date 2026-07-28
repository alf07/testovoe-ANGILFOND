<?php

namespace Database\Factories;

use App\Models\UserChannel;
use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserChannelFactory extends Factory
{
    protected $model = UserChannel::class;

    public function definition()
    {
        return [
            'user_id' => Users::factory(),
            'channel' => $this->faker->randomElement(['email', 'telegram']),
            'destination' => $this->faker->email,
        ];
    }
}
