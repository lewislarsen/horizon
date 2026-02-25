<?php

namespace Laravel\Horizon\Tests\Controller;

use Laravel\Horizon\Tests\ControllerTest;

class QueueControllerTest extends ControllerTest
{
    public function test_can_get_queue_connections()
    {
        $response = $this->actingAs(new Fakes\User)
                    ->get('/horizon/api/queues');

        $response->assertStatus(200);
    }

    public function test_can_clear_queue()
    {
        $response = $this->actingAs(new Fakes\User)
                    ->post('/horizon/api/queues/clear', [
                        'connection' => 'redis',
                        'queue' => 'emails',
                    ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cleared',
            'queue',
            'connection',
        ]);
        $response->assertJson([
            'queue' => 'emails',
            'connection' => 'redis',
        ]);
    }

    public function test_clear_queue_requires_connection_parameter()
    {
        $response = $this->actingAs(new Fakes\User)
                    ->post('/horizon/api/queues/clear', [
                        'queue' => 'emails',
                    ]);

        $response->assertStatus(302);
    }

    public function test_clear_queue_requires_queue_parameter()
    {
        $response = $this->actingAs(new Fakes\User)
                    ->post('/horizon/api/queues/clear', [
                        'connection' => 'redis',
                    ]);

        $response->assertStatus(302);
    }

    public function test_clear_queue_returns_zero_when_queue_is_empty()
    {
        $response = $this->actingAs(new Fakes\User)
                    ->post('/horizon/api/queues/clear', [
                        'connection' => 'redis',
                        'queue' => 'emails',
                    ]);

        $response->assertJsonStructure([
            'cleared',
            'queue',
            'connection',
        ]);
    }
}
