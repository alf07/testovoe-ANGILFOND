<?php

namespace Tests\Unit\Models;

use App\Models\UserChannel;
use App\Models\Users;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_channels_relation()
    {
        $user = Users::factory()->create();
        $channel = UserChannel::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->channels->contains($channel));
        $this->assertInstanceOf(Collection::class, $user->channels);
    }
}
