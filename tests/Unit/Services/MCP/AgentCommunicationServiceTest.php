<?php

declare(strict_types=1);

namespace Tests\Unit\Services\MCP;

use App\Services\MCP\AgentCommunicationService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AgentCommunicationServiceTest extends TestCase
{
    protected AgentCommunicationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AgentCommunicationService;
        Cache::flush();
    }

    public function test_sends_message_successfully(): void
    {
        $message = $this->service->sendMessage(
            'agent1',
            'agent2',
            AgentCommunicationService::MSG_REQUEST,
            ['data' => 'test'],
            AgentCommunicationService::PRIORITY_HIGH
        );

        /** @var array{from: string, to: string, type: string, priority: string, status: string, id: string} $message */
        $this->assertArrayHasKey('id', $message);
        $this->assertEquals('agent1', $message['from']);
        $this->assertEquals('agent2', $message['to']);
        $this->assertEquals(AgentCommunicationService::MSG_REQUEST, $message['type']);
        $this->assertEquals(AgentCommunicationService::PRIORITY_HIGH, $message['priority']);
        $this->assertEquals('sent', $message['status']);
    }

    public function test_broadcasts_message_to_multiple_agents(): void
    {
        $messages = $this->service->broadcastMessage(
            'agent1',
            ['agent2', 'agent3', 'agent4'],
            ['broadcast' => 'data']
        );

        /** @var array<int, array{from: string, type: string}> $messages */
        $this->assertCount(3, $messages);

        foreach ($messages as $message) {
            $this->assertEquals('agent1', $message['from']);
            $this->assertEquals(AgentCommunicationService::MSG_BROADCAST, $message['type']);
        }
    }

    public function test_receives_messages_for_agent(): void
    {
        // Send some messages
        $this->service->sendMessage('agent1', 'agent2', AgentCommunicationService::MSG_REQUEST, ['msg' => '1']);
        $this->service->sendMessage('agent3', 'agent2', AgentCommunicationService::MSG_NOTIFICATION, ['msg' => '2']);

        $messages = $this->service->receiveMessages('agent2');

        /** @var array<int, array<string, mixed>> $messages */
        $this->assertCount(2, $messages);
    }

    public function test_prioritizes_messages_correctly(): void
    {
        // Send messages with different priorities
        $this->service->sendMessage('agent1', 'agent2', AgentCommunicationService::MSG_REQUEST, ['msg' => 'low'], AgentCommunicationService::PRIORITY_LOW);
        $this->service->sendMessage('agent1', 'agent2', AgentCommunicationService::MSG_REQUEST, ['msg' => 'high'], AgentCommunicationService::PRIORITY_HIGH);
        $this->service->sendMessage('agent1', 'agent2', AgentCommunicationService::MSG_REQUEST, ['msg' => 'normal'], AgentCommunicationService::PRIORITY_NORMAL);

        $messages = $this->service->receiveMessages('agent2');

        // High priority should be first
        $this->assertEquals('high', $messages[0]['payload']['msg']);
    }

    public function test_marks_message_as_read(): void
    {
        $message = $this->service->sendMessage('agent1', 'agent2', AgentCommunicationService::MSG_REQUEST, ['data' => 'test']);

        $result = $this->service->markAsRead('agent2', $message['id']);

        $this->assertTrue($result);
    }

    public function test_shares_data_between_agents(): void
    {
        $result = $this->service->shareData('agent1', 'agent2', 'shared_key', ['value' => 'data']);

        $this->assertTrue($result);

        $retrievedData = $this->service->getSharedData('agent1', 'agent2', 'shared_key');

        $this->assertEquals(['value' => 'data'], $retrievedData);
    }

    public function test_creates_shared_context(): void
    {
        $context = $this->service->createSharedContext('context1', ['initial' => 'data']);

        /** @var array{data: array<string, mixed>, id: string, participants: array<int, string>} $context */
        $this->assertEquals('context1', $context['id']);
        $this->assertArrayHasKey('data', $context);
        $this->assertEquals(['initial' => 'data'], $context['data']);
        $this->assertArrayHasKey('participants', $context);
    }

    public function test_agent_joins_shared_context(): void
    {
        $this->service->createSharedContext('context1');

        $result = $this->service->joinSharedContext('context1', 'agent1');

        $this->assertTrue($result);

        $context = $this->service->getSharedContext('context1');
        if (! is_array($context)) {
            $this->fail('Context not found.');
        }
        /** @var array{participants: array<int, string>} $context */
        $this->assertContains('agent1', $context['participants']);
    }

    public function test_updates_shared_context(): void
    {
        $this->service->createSharedContext('context1', ['key1' => 'value1']);
        $this->service->joinSharedContext('context1', 'agent1');

        $result = $this->service->updateSharedContext('context1', 'agent1', ['key2' => 'value2']);

        $this->assertTrue($result);

        $context = $this->service->getSharedContext('context1');
        if (! is_array($context)) {
            $this->fail('Context not found.');
        }
        /** @var array{data: array<string, mixed>, last_updated_by: string} $context */
        $this->assertEquals('value1', $context['data']['key1']);
        $this->assertEquals('value2', $context['data']['key2']);
        $this->assertEquals('agent1', $context['last_updated_by']);
    }

    public function test_throws_exception_when_updating_nonexistent_context(): void
    {
        $result = $this->service->updateSharedContext('nonexistent', 'agent1', ['data' => 'test']);

        $this->assertFalse($result);
    }

    public function test_throws_exception_when_non_participant_updates_context(): void
    {
        $this->service->createSharedContext('context1');

        $result = $this->service->updateSharedContext('context1', 'agent1', ['data' => 'test']);

        $this->assertFalse($result);
    }
}
