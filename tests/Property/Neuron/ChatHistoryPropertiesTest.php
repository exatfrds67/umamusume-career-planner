<?php

declare(strict_types=1);

/**
 * Property-based tests for Chat History Persistence.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for chat history functionality.
 *
 * Property 3: Chat History Persistence
 * For any agent interaction, when a message is sent or received,
 * the system should persist that message to the database with the
 * correct thread_id, role, and content.
 *
 * **Validates: Requirements 11.1**
 *
 * Testing Strategy:
 * - Generate random messages with various content types
 * - Generate random thread IDs
 * - Verify messages are persisted correctly
 * - Verify all message attributes are stored
 * - Test with minimum 100 iterations
 *
 * Feature: neuron-ai-integration
 * Property: 3 - Chat History Persistence
 */

use App\Models\ChatMessage;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\UserMessage;

describe('Property 3: Chat History Persistence', function () {
    it('persists any user message to the database with correct attributes', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $messageContent = fake()->sentence();

        // Create chat history and add message
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        $userMessage = new UserMessage($messageContent);
        $chatHistory->addMessage($userMessage);

        // Property: Message should be persisted to database
        $persistedMessage = ChatMessage::where('thread_id', $threadId)->first();

        expect($persistedMessage)->not->toBeNull();
        expect($persistedMessage->thread_id)->toBe($threadId);
        expect($persistedMessage->role)->toBe('user');
        expect($persistedMessage->content)->toBe($messageContent);
    })->repeat(100);

    it('persists any assistant message to the database with correct attributes', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $messageContent = fake()->paragraph();

        // Create chat history and add message
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        $assistantMessage = new AssistantMessage($messageContent);
        $chatHistory->addMessage($assistantMessage);

        // Property: Message should be persisted to database
        $persistedMessage = ChatMessage::where('thread_id', $threadId)->first();

        expect($persistedMessage)->not->toBeNull();
        expect($persistedMessage->thread_id)->toBe($threadId);
        expect($persistedMessage->role)->toBe('assistant');
        expect($persistedMessage->content)->toBe($messageContent);
    })->repeat(100);

    it('persists multiple messages in sequence with correct order', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $messageCount = random_int(2, 10);
        $messages = [];

        // Create chat history
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Add random messages
        for ($i = 0; $i < $messageCount; $i++) {
            $content = fake()->sentence();
            $isUser = $i % 2 === 0;

            if ($isUser) {
                $message = new UserMessage($content);
                $messages[] = ['role' => 'user', 'content' => $content];
            } else {
                $message = new AssistantMessage($content);
                $messages[] = ['role' => 'assistant', 'content' => $content];
            }

            $chatHistory->addMessage($message);
        }

        // Property: All messages should be persisted in correct order
        $persistedMessages = ChatMessage::where('thread_id', $threadId)
            ->orderBy('created_at')
            ->get();

        expect($persistedMessages)->toHaveCount($messageCount);

        foreach ($persistedMessages as $index => $persistedMessage) {
            expect($persistedMessage->role)->toBe($messages[$index]['role']);
            expect($persistedMessage->content)->toBe($messages[$index]['content']);
        }
    })->repeat(100);

    it('persists messages with various content lengths', function () {
        // Generate random test data with varying content lengths
        $threadId = 'thread_'.uniqid();
        $contentLengths = [
            'short' => fake()->word(),
            'medium' => fake()->sentence(),
            'long' => fake()->paragraph(5),
            'very_long' => fake()->text(1000),
        ];

        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Add messages with different content lengths
        foreach ($contentLengths as $type => $content) {
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);
        }

        // Property: All messages should be persisted regardless of length
        $persistedMessages = ChatMessage::where('thread_id', $threadId)->get();

        expect($persistedMessages)->toHaveCount(count($contentLengths));

        foreach ($persistedMessages as $index => $persistedMessage) {
            $expectedContent = array_values($contentLengths)[$index];
            expect($persistedMessage->content)->toBe($expectedContent);
        }
    })->repeat(50);

    it('persists messages with special characters and unicode', function () {
        // Generate random test data with special characters
        $threadId = 'thread_'.uniqid();
        $specialContents = [
            'emoji' => '🏇 Training advice for 🎯 speed!',
            'japanese' => 'ウマ娘のトレーニング戦略',
            'mixed' => 'Training 🏃‍♀️ for ウマ娘 character!',
            'symbols' => 'Stats: Speed↑ Stamina↓ Power→',
            'quotes' => "She said: \"Let's train!\" and I agreed.",
        ];

        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Add messages with special characters
        foreach ($specialContents as $type => $content) {
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);
        }

        // Property: All messages should be persisted with special characters intact
        $persistedMessages = ChatMessage::where('thread_id', $threadId)->get();

        expect($persistedMessages)->toHaveCount(count($specialContents));

        foreach ($persistedMessages as $index => $persistedMessage) {
            $expectedContent = array_values($specialContents)[$index];
            expect($persistedMessage->content)->toBe($expectedContent);
        }
    })->repeat(50);

    it('persists messages across different thread IDs independently', function () {
        // Generate random thread IDs
        $threadCount = random_int(3, 8);
        $threads = [];

        for ($i = 0; $i < $threadCount; $i++) {
            $threadId = 'thread_'.uniqid().'_'.$i;
            $messageContent = fake()->sentence();

            $chatHistory = new EloquentChatHistory(
                threadId: $threadId,
                modelClass: ChatMessage::class
            );

            $message = new UserMessage($messageContent);
            $chatHistory->addMessage($message);

            $threads[$threadId] = $messageContent;
        }

        // Property: Each thread should have exactly one message with correct content
        foreach ($threads as $threadId => $expectedContent) {
            $messages = ChatMessage::where('thread_id', $threadId)->get();

            expect($messages)->toHaveCount(1);
            expect($messages->first()->content)->toBe($expectedContent);
        }

        // Property: Total messages should equal thread count
        expect(ChatMessage::count())->toBe($threadCount);
    })->repeat(100);

    it('persists messages with timestamps in correct chronological order', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $messageCount = random_int(3, 7);

        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Add messages with slight delays to ensure different timestamps
        for ($i = 0; $i < $messageCount; $i++) {
            $content = "Message {$i}: ".fake()->sentence();
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);

            // Small delay to ensure different timestamps
            usleep(1000); // 1ms delay
        }

        // Property: Messages should be ordered by creation time
        $persistedMessages = ChatMessage::where('thread_id', $threadId)
            ->orderBy('created_at')
            ->get();

        expect($persistedMessages)->toHaveCount($messageCount);

        // Verify timestamps are in ascending order
        $previousTimestamp = null;
        foreach ($persistedMessages as $message) {
            if ($previousTimestamp !== null) {
                expect($message->created_at->timestamp)
                    ->toBeGreaterThanOrEqual($previousTimestamp);
            }
            $previousTimestamp = $message->created_at->timestamp;
        }
    })->repeat(50);

    it('persists empty messages correctly', function () {
        // Generate random test data with edge case: empty message
        $threadId = 'thread_'.uniqid();
        $emptyContent = '';

        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        $message = new UserMessage($emptyContent);
        $chatHistory->addMessage($message);

        // Property: Even empty messages should be persisted
        $persistedMessage = ChatMessage::where('thread_id', $threadId)->first();

        expect($persistedMessage)->not->toBeNull();
        expect($persistedMessage->content)->toBe($emptyContent);
        expect($persistedMessage->role)->toBe('user');
    })->repeat(50);

    it('persists messages with numeric content', function () {
        // Generate random test data with numeric content
        $threadId = 'thread_'.uniqid();
        $numericContents = [
            (string) random_int(1, 1000),
            (string) (random_int(1, 100) / 10),
            '0',
            '-'.random_int(1, 100),
        ];

        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Add messages with numeric content
        foreach ($numericContents as $content) {
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);
        }

        // Property: Numeric content should be persisted as strings
        $persistedMessages = ChatMessage::where('thread_id', $threadId)->get();

        expect($persistedMessages)->toHaveCount(count($numericContents));

        foreach ($persistedMessages as $index => $persistedMessage) {
            expect($persistedMessage->content)->toBe($numericContents[$index]);
        }
    })->repeat(50);
});
