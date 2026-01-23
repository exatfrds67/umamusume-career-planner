<?php

declare(strict_types=1);

/**
 * Property-based tests for Chat History Scoping.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for chat history scoping functionality.
 *
 * Property 6: Chat History Scoping
 * For any chat history query, when filtered by thread_id, the system
 * should return only messages matching that thread_id.
 *
 * **Validates: Requirements 11.4**
 *
 * Testing Strategy:
 * - Create random chat histories with different thread IDs
 * - Verify retrieval returns only messages for specified thread
 * - Test with varying message counts per thread
 * - Test with minimum 100 iterations
 *
 * Feature: neuron-ai-integration
 * Property: 6 - Chat History Scoping
 */

use App\Models\ChatMessage;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Chat\Messages\UserMessage;

describe('Property 6: Chat History Scoping', function () {
    it('retrieves only messages for the specified thread ID', function () {
        // Generate random thread IDs
        $threadA = 'thread_'.uniqid().'_a';
        $threadB = 'thread_'.uniqid().'_b';

        $messagesInA = random_int(2, 5);
        $messagesInB = random_int(2, 5);

        // Add messages to thread A
        $chatHistoryA = new EloquentChatHistory(
            threadId: $threadA,
            modelClass: ChatMessage::class
        );

        for ($i = 0; $i < $messagesInA; $i++) {
            $chatHistoryA->addMessage(new UserMessage("Thread A message {$i}"));
        }

        // Add messages to thread B
        $chatHistoryB = new EloquentChatHistory(
            threadId: $threadB,
            modelClass: ChatMessage::class
        );

        for ($i = 0; $i < $messagesInB; $i++) {
            $chatHistoryB->addMessage(new UserMessage("Thread B message {$i}"));
        }

        // Property: Retrieving thread A should only return thread A messages
        $retrievedA = (new EloquentChatHistory($threadA, ChatMessage::class))->getMessages();
        expect($retrievedA)->toHaveCount($messagesInA);

        foreach ($retrievedA as $index => $message) {
            expect($message->getContent())->toBe("Thread A message {$index}");
        }

        // Property: Retrieving thread B should only return thread B messages
        $retrievedB = (new EloquentChatHistory($threadB, ChatMessage::class))->getMessages();
        expect($retrievedB)->toHaveCount($messagesInB);

        foreach ($retrievedB as $index => $message) {
            expect($message->getContent())->toBe("Thread B message {$index}");
        }
    })->repeat(100);

    it('returns empty array for non-existent thread IDs', function () {
        // Generate random non-existent thread ID
        $nonExistentThread = 'nonexistent_'.uniqid();

        // Property: Non-existent threads should return empty array
        $chatHistory = new EloquentChatHistory(
            threadId: $nonExistentThread,
            modelClass: ChatMessage::class
        );

        $messages = $chatHistory->getMessages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveCount(0);
    })->repeat(100);

    it('maintains thread isolation with multiple threads', function () {
        // Generate random number of threads
        $threadCount = random_int(3, 6);
        $threads = [];

        // Create messages in multiple threads
        for ($i = 0; $i < $threadCount; $i++) {
            $threadId = 'thread_'.uniqid().'_'.$i;
            $messageCount = random_int(1, 4);

            $chatHistory = new EloquentChatHistory(
                threadId: $threadId,
                modelClass: ChatMessage::class
            );

            for ($j = 0; $j < $messageCount; $j++) {
                $chatHistory->addMessage(new UserMessage("Thread {$i} Message {$j}"));
            }

            $threads[$threadId] = $messageCount;
        }

        // Property: Each thread should retrieve only its own message count
        foreach ($threads as $threadId => $expectedCount) {
            $retrieved = (new EloquentChatHistory($threadId, ChatMessage::class))->getMessages();
            expect($retrieved)->toHaveCount($expectedCount);
        }
    })->repeat(100);

    it('scopes messages correctly after adding to existing thread', function () {
        // Generate random thread IDs
        $threadA = 'thread_'.uniqid().'_a';
        $threadB = 'thread_'.uniqid().'_b';

        // Add initial messages to both threads
        $chatHistoryA1 = new EloquentChatHistory($threadA, ChatMessage::class);
        $chatHistoryA1->addMessage(new UserMessage('Thread A initial'));

        $chatHistoryB1 = new EloquentChatHistory($threadB, ChatMessage::class);
        $chatHistoryB1->addMessage(new UserMessage('Thread B initial'));

        // Add more messages to thread A only
        $additionalMessages = random_int(2, 5);
        for ($i = 0; $i < $additionalMessages; $i++) {
            $chatHistoryA1->addMessage(new UserMessage("Thread A additional {$i}"));
        }

        // Property: Thread A should have 1 + additional messages
        $retrievedA = (new EloquentChatHistory($threadA, ChatMessage::class))->getMessages();
        expect($retrievedA)->toHaveCount(1 + $additionalMessages);

        // Property: Thread B should still have only 1 message
        $retrievedB = (new EloquentChatHistory($threadB, ChatMessage::class))->getMessages();
        expect($retrievedB)->toHaveCount(1);
        expect($retrievedB[0]->getContent())->toBe('Thread B initial');
    })->repeat(100);

    it('handles thread IDs with special characters', function () {
        // Generate thread IDs with special characters
        $specialChars = ['_', '-', '.', ':', '@'];
        $char = $specialChars[array_rand($specialChars)];
        $threadId = 'thread'.$char.uniqid().$char.'special';

        // Add messages to thread with special characters
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        $messageCount = random_int(2, 5);
        for ($i = 0; $i < $messageCount; $i++) {
            $chatHistory->addMessage(new UserMessage("Message {$i}"));
        }

        // Property: Should retrieve messages correctly despite special characters
        $retrieved = (new EloquentChatHistory($threadId, ChatMessage::class))->getMessages();
        expect($retrieved)->toHaveCount($messageCount);
    })->repeat(50);
});
