<?php

namespace Tests\Feature\Services;

use App\DTO\NotificationData;
use App\Jobs\SendNotificationJob;
use App\Models\History;
use App\Models\UserChannel;
use App\Models\Users;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    public function test_find_user_returns_user()
    {
        $user = Users::factory()->create();
        $found = $this->service->findUser($user->id);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_user_throws_if_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->findUser(999);
    }

    public function test_save_history_creates_record()
    {
        $user = Users::factory()->create();
        $history = $this->service->saveHistory(
            $user->id,
            'Test message',
            'email',
            'test@example.com'
        );

        $this->assertInstanceOf(History::class, $history);
        $this->assertDatabaseHas('histories', [
            'user_id' => $user->id,
            'channel' => 'email',
            'status' => 'processing',
            'message' => 'Test message',
            'destination' => 'test@example.com',
            'attempts' => 0,
        ]);
    }

    public function test_send_dispatches_jobs_for_each_user_channel()
    {
        Queue::fake();

        $user = Users::factory()->create();
        UserChannel::factory()->create(['user_id' => $user->id, 'channel' => 'email', 'destination' => 'a@b.com']);
        UserChannel::factory()->create(['user_id' => $user->id, 'channel' => 'telegram', 'destination' => '12345']);

        $dto = new NotificationData(
            user_id: $user->id,
            message: 'Hello'
        );

        $this->service->send($dto);

        Queue::assertPushed(SendNotificationJob::class, 2);

        $this->assertDatabaseCount('histories', 2);
        $this->assertDatabaseHas('histories', ['user_id' => $user->id, 'channel' => 'email', 'destination' => 'a@b.com']);
        $this->assertDatabaseHas('histories', ['user_id' => $user->id, 'channel' => 'telegram', 'destination' => '12345']);
    }
}
