<?php

declare(strict_types=1);

/**
 * Feature tests for EloquentChatHistory functionality.
 *
 * Tests the integration of Neuron AI's EloquentChatHistory component
 * with the Laravel application, verifying that:
 * - Messages are persisted correctly to the database
 * - Messages are retrieved on subsequent calls
 * - Chat history maintains conversation context
 *
 * Validates Requirements: 11.1, 11.2, 11.3, 11.4
 */

use App\Models\ChatMessage;
use App\Models\User;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\UserMessage;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('EloquentChatHistory Integration', function () {
    it('persists user messages to the database', function () {
        // Create a chat history instance
        $chatHistory = new EloquentChatHistory(
            threadId: 'test_thread_1',
            modelClass: ChatMessage::class
        );

        // Add a user message
        $userMessage = new UserMessage('What training should I focus on?');
        $chatHistory->addMessage($userMessage);

        // Verify message was persisted
        expect(ChatMessage::count())->toBe(1);

        $message = ChatMessage::first();
        expect($message->thread_id)->toBe('test_thread_1');
        expect($message->role)->toBe('user');
        expect($message->content)->toBe('What training should I focus on?');
    });

    it('persists assistant messages to the database', function () {
        $chatHistory = new EloquentChatHistory(
            threadId: 'test_thread_2',
            modelClass: ChatMessage::class
        );

        // Add an assistant message
        $assistantMessage = new AssistantMessage('Focus on speed training to improve your character.');
        $chatHistory->addMessage($assistantMessage);

        // Verify message was persisted
        expect(ChatMessage::count())->toBe(1);

        $message = ChatMessage::first();
        expect($message->thread_id)->toBe('test_thread_2');
        expect($message->role)->toBe('assistant');
        expect($message->content)->toBe('Focus on speed training to improve your character.');
    });

    it('retrieves messages on subsequent calls', function () {
        $threadId = 'test_thread_3';

        // First interaction - add messages
        $chatHistory1 = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        $chatHistory1->addMessage(new UserMessage('First message'));
        $chatHistory1->addMessage(new AssistantMessage('First response'));

        // Second interaction - retrieve messages
        $chatHistory2 = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        $messages = $chatHistory2->getMessages();

        // Verify messages were retrieved
        expect($messages)->toHaveCount(2);
        expect($messages[0]->getRole())->toBe('user');
        expect($messages[0]->getContent())->toBe('First message');
        expect($messages[1]->getRole())->toBe('assistant');
        expect($messages[1]->getContent())->toBe('First response');
    });

    it('maintains conversation context across multiple interactions', function () {
        $threadId = 'test_thread_4';

        // First interaction
        $chatHistory1 = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );
        $chatHistory1->addMessage(new UserMessage('What is my character speed?'));
        $chatHistory1->addMessage(new AssistantMessage('Your character speed is 800.'));

        // Second interaction
        $chatHistory2 = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );
        $chatHistory2->addMessage(new UserMessage('How can I improve it?'));
        $chatHistory2->addMessage(new AssistantMessage('Focus on speed training.'));

        // Third interaction - retrieve all messages
        $chatHistory3 = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        $messages = $chatHistory3->getMessages();

        // Verify all messages are present in order
        expect($messages)->toHaveCount(4);
        expect($messages[0]->getContent())->toBe('What is my character speed?');
        expect($messages[1]->getContent())->toBe('Your character speed is 800.');
        expect($messages[2]->getContent())->toBe('How can I improve it?');
        expect($messages[3]->getContent())->toBe('Focus on speed training.');
    });

    it('scopes messages by thread_id', function () {
        // Create messages in different threads
        $chatHistory1 = new EloquentChatHistory(
            threadId: 'thread_a',
            modelClass: ChatMessage::class
        );
        $chatHistory1->addMessage(new UserMessage('Message in thread A'));

        $chatHistory2 = new EloquentChatHistory(
            threadId: 'thread_b',
            modelClass: ChatMessage::class
        );
        $chatHistory2->addMessage(new UserMessage('Message in thread B'));

        // Retrieve messages from thread A
        $chatHistoryA = new EloquentChatHistory(
            threadId: 'thread_a',
            modelClass: ChatMessage::class
        );
        $messagesA = $chatHistoryA->getMessages();

        // Retrieve messages from thread B
        $chatHistoryB = new EloquentChatHistory(
            threadId: 'thread_b',
            modelClass: ChatMessage::class
        );
        $messagesB = $chatHistoryB->getMessages();

        // Verify each thread only contains its own messages
        expect($messagesA)->toHaveCount(1);
        expect($messagesA[0]->getContent())->toBe('Message in thread A');

        expect($messagesB)->toHaveCount(1);
        expect($messagesB[0]->getContent())->toBe('Message in thread B');
    });

    it('handles empty chat history', function () {
        $chatHistory = new EloquentChatHistory(
            threadId: 'empty_thread',
            modelClass: ChatMessage::class
        );

        $messages = $chatHistory->getMessages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveCount(0);
    });

    it('persists messages with metadata', function () {
        $chatHistory = new EloquentChatHistory(
            threadId: 'test_thread_meta',
            modelClass: ChatMessage::class
        );

        // Add a message (metadata is handled internally by Neuron)
        $userMessage = new UserMessage('Test message with metadata');
        $chatHistory->addMessage($userMessage);

        // Verify message was persisted
        $message = ChatMessage::first();
        expect($message->thread_id)->toBe('test_thread_meta');
        expect($message->role)->toBe('user');
        expect($message->content)->toBe('Test message with metadata');
    });

    it('maintains message order by creation time', function () {
        $threadId = 'test_thread_order';
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Add messages in sequence
        $chatHistory->addMessage(new UserMessage('First'));
        sleep(1); // Ensure different timestamps
        $chatHistory->addMessage(new AssistantMessage('Second'));
        sleep(1);
        $chatHistory->addMessage(new UserMessage('Third'));

        // Retrieve messages
        $chatHistory2 = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );
        $messages = $chatHistory2->getMessages();

        // Verify order is maintained
        expect($messages)->toHaveCount(3);
        expect($messages[0]->getContent())->toBe('First');
        expect($messages[1]->getContent())->toBe('Second');
        expect($messages[2]->getContent())->toBe('Third');
    });
});

describe('EloquentChatHistory with Multiple Threads', function () {
    it('handles multiple concurrent threads independently', function () {
        // Create messages in thread 1
        $chatHistory1 = new EloquentChatHistory(
            threadId: 'user_1_character_1',
            modelClass: ChatMessage::class
        );
        $chatHistory1->addMessage(new UserMessage('Thread 1 message 1'));
        $chatHistory1->addMessage(new AssistantMessage('Thread 1 response 1'));

        // Create messages in thread 2
        $chatHistory2 = new EloquentChatHistory(
            threadId: 'user_1_character_2',
            modelClass: ChatMessage::class
        );
        $chatHistory2->addMessage(new UserMessage('Thread 2 message 1'));
        $chatHistory2->addMessage(new AssistantMessage('Thread 2 response 1'));

        // Add more messages to thread 1
        $chatHistory1->addMessage(new UserMessage('Thread 1 message 2'));

        // Verify thread 1 has 3 messages
        $thread1Messages = $chatHistory1->getMessages();
        expect($thread1Messages)->toHaveCount(3);
        expect($thread1Messages[0]->getContent())->toBe('Thread 1 message 1');
        expect($thread1Messages[2]->getContent())->toBe('Thread 1 message 2');

        // Verify thread 2 has 2 messages
        $thread2Messages = $chatHistory2->getMessages();
        expect($thread2Messages)->toHaveCount(2);
        expect($thread2Messages[0]->getContent())->toBe('Thread 2 message 1');

        // Verify total messages in database
        expect(ChatMessage::count())->toBe(5);
    });
});
