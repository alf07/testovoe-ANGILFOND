<?php

namespace Tests\Feature\Http\Controllers;

use App\Enum\NotificationStatus;
use App\Models\History;
use App\Models\UserChannel;
use App\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_notification_validates_required_fields()
    {
        $response = $this->postJson('/api/notifications', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'message']);
    }

    public function test_send_notification_calls_service_and_returns_success()
    {
        Queue::fake();

        $user = Users::factory()->create();
        UserChannel::factory()->create(['user_id' => $user->id]);

        $data = [
            'user_id' => $user->id,
            'message' => 'Hello',
        ];

        $response = $this->postJson('/api/notifications', $data);
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('histories', [
            'user_id' => $user->id,
            'message' => 'Hello',
        ]);
    }

    public function test_get_status_notification_by_id_returns_status()
    {
        $history = History::factory()->create([
            'status' => NotificationStatus::Sent,
        ]);

        $response = $this->getJson("/api/notifications/{$history->id}");
        $response->assertStatus(200)
            ->assertJson(['status' => 'sent']);
    }

    public function test_get_status_returns_404_if_not_found()
    {
        $response = $this->getJson('/api/notifications/999');
        $response->assertStatus(404);
    }

    public function test_get_history_by_user_id_returns_history_list()
    {
        $user = Users::factory()->create();
        History::factory()->count(3)->create(['user_id' => $user->id]);
        History::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}/notifications");
        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_get_history_can_filter_by_status()
    {
        $user = Users::factory()->create();
        History::factory()->create(['user_id' => $user->id, 'status' => NotificationStatus::Sent]);
        History::factory()->create(['user_id' => $user->id, 'status' => NotificationStatus::Processing]);

        $response = $this->getJson("/api/users/{$user->id}/notifications?status=sent");
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['status' => 'sent']);
    }

    public function test_get_history_can_filter_by_channel()
    {
        $user = Users::factory()->create();
        History::factory()->create(['user_id' => $user->id, 'channel' => 'email']);
        History::factory()->create(['user_id' => $user->id, 'channel' => 'telegram']);

        $response = $this->getJson("/api/users/{$user->id}/notifications?channel=email");
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['channel' => 'email']);
    }

    public function test_get_history_validation_fails_on_invalid_status()
    {
        $user = Users::factory()->create();
        $response = $this->getJson("/api/users/{$user->id}/notifications?status=invalid");
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
