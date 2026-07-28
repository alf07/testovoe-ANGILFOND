<?php

namespace Tests\Unit\Jobs;

use App\Contracts\NotificationChannelInterface;
use App\Enum\NotificationStatus;
use App\Factory\NotificationChannelFactory;
use App\Jobs\SendNotificationJob;
use App\Models\History;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SendNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_marks_as_sent_on_success()
    {
        $history = History::factory()->create([
            'status' => NotificationStatus::Processing,
            'attempts' => 0,
        ]);

        $mockChannel = Mockery::mock(NotificationChannelInterface::class);
        $mockChannel->shouldReceive('send')
            ->once()
            ->andReturn(true);

        $mockFactory = Mockery::mock(NotificationChannelFactory::class);
        $mockFactory->shouldReceive('make')
            ->once()
            ->with($history->channel)
            ->andReturn($mockChannel);

        $job = new SendNotificationJob($history);
        $job->handle($mockFactory);

        $history->refresh();
        $this->assertEquals(NotificationStatus::Sent, $history->status);
        $this->assertNotNull($history->sent_at);
    }

    public function test_job_handles_failure_and_retries()
    {
        $history = History::factory()->create([
            'status' => NotificationStatus::Processing,
            'attempts' => 0,
        ]);

        $mockChannel = Mockery::mock(NotificationChannelInterface::class);
        $mockChannel->shouldReceive('send')->andReturn(false);

        $mockFactory = Mockery::mock(NotificationChannelFactory::class);
        $mockFactory->shouldReceive('make')->andReturn($mockChannel);

        $job = new SendNotificationJob($history);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Повторная отправка (попытка 1): Канал вернул false');

        $job->handle($mockFactory);

        $history->refresh();
        $this->assertEquals(1, $history->attempts);
        $this->assertEquals(NotificationStatus::Processing, $history->status);
    }

    public function test_job_marks_as_error_after_max_attempts()
    {
        $history = History::factory()->create([
            'status' => NotificationStatus::Processing,
            'attempts' => 2,
        ]);

        $mockChannel = Mockery::mock(NotificationChannelInterface::class);
        $mockChannel->shouldReceive('send')->andReturn(false);

        $mockFactory = Mockery::mock(NotificationChannelFactory::class);
        $mockFactory->shouldReceive('make')->andReturn($mockChannel);

        $job = new SendNotificationJob($history);
        $job->handle($mockFactory);

        $history->refresh();
        $this->assertEquals(3, $history->attempts);
        $this->assertEquals(NotificationStatus::Error, $history->status);
        $this->assertEquals('Канал вернул false', $history->error_message);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
