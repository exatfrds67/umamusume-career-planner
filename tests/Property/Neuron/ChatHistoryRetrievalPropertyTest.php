<?php

declare(strict_types=1);

/**
 * Property-based tests for Chat History Retrieval.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for chat history retrieval functionality.
 *
 * Property 4: Chat History Retrieval
 * For any continuing conversation, when previous messages exist in the database,
 * the system should load and include them in the agent's context.
 *
 * **Validates: Requirements 11.2**
 *
 * Testing Strategy:
 * - Create random chat histories with varying message counts
 * - Verify retrieval includes all previous messages
 * - Verify messages are retrieved in correct order
 * - Test with different thread IDs
 * - Test with minimum 100 iterations
 *
 * Feature: neuron-ai-integration
 * Property: 4 - Chat History Retrieval
 */

use App\Models\ChatMessage;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\UserMessage;

describe('Property 4: Chat History Retrieval', function () {
    it('retrieves all previous messages for any thread ID', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $messageCount = random_int(2, 10);
        $expectedMessages = [];

        // Create chat history and add messages
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        for ($i = 0; $i < $messageCount; $i++) {
            $content = "Message {$i}: ".fake()->sentence();
            $isUser = $i % 2 === 0;

            if ($isUser) {
                $message = new UserMessage($content);
                $expectedMessages[] = ['role' => 'user', 'content' => $content];
            } else {
                $message = new AssistantMessage($content);
                $expectedMessages[] = ['role' => 'assistant', 'content' => $content];
            }

            $chatHistory->addMessage($message);
        }

        // Create new chat history instance to test retrieval
        $newChatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Property: All previous messages should be retrievable
        $retrievedMessages = $newChatHistory->getMessages();

        expect($retrievedMessages)->toHaveCount($messageCount);

        foreach ($retrievedMessages as $index => $retrievedMessage) {
            expect($retrievedMessage->getRole())->toBe($expectedMessages[$index]['role']);
            expect($retrievedMessage->getContent())->toBe($expectedMessages[$index]['content']);
        }
    })->repeat(100);

    it('retrieves messages in chronological order', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $messageCount = random_int(3, 8);

        // Create chat history and add messages with delays
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        for ($i = 0; $i < $messageCount; $i++) {
            $content = "Message {$i}";
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);
            usleep(1000); // Ensure different timestamps
        }

        // Create new chat history instance to test retrieval
        $newChatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Property: Messages should be retrieved in chronological order
        $retrievedMessages = $newChatHistory->getMessages();

        expect($retrievedMessages)->toHaveCount($messageCount);

        for ($i = 0; $i < $messageCount; $i++) {
            expect($retrievedMessages[$i]->getContent())->toBe("Message {$i}");
        }
    })->repeat(100);

    it('retrieves only messages for the specified thread ID', function () {
        // Generate random test data for multiple threads
        $threadCount = random_int(3, 6);
        $threads = [];

        for ($i = 0; $i < $threadCount; $i++) {
            $threadId = 'thread_'.uniqid().'_'.$i;
            $messageCount = random_int(2, 5);

            $chatHistory = new EloquentChatHistory(
                threadId: $threadId,
                modelClass: ChatMessage::class
            );

            $messages = [];
            for ($j = 0; $j < $messageCount; $j++) {
                $content = "Thread {$i} Message {$j}";
                $message = new UserMessage($content);
                $chatHistory->addMessage($message);
                $messages[] = $content;
            }

            $threads[$threadId] = $messages;
        }

        // Property: Each thread should retrieve only its own messages
        foreach ($threads as $threadId => $expectedMessages) {
            $chatHistory = new EloquentChatHistory(
                threadId: $threadId,
                modelClass: ChatMessage::class
            );

            $retrievedMessages = $chatHistory->getMessages();

            expect($retrievedMessages)->toHaveCount(count($expectedMessages));

            foreach ($retrievedMessages as $index => $retrievedMessage) {
                expect($retrievedMessage->getContent())->toBe($expectedMessages[$index]);
            }
        }
    })->repeat(100);

    it('retrieves empty array when no messages exist for thread', function () {
        // Generate random thread ID that has no messages
        $threadId = 'nonexistent_thread_'.uniqid();

        // Create chat history for non-existent thread
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Property: Should return empty array for threads with no messages
        $retrievedMessages = $chatHistory->getMessages();

        expect($retrievedMessages)->toBeArray();
        expect($retrievedMessages)->toHaveCount(0);
    })->repeat(100);

    it('retrieves messages with all attributes intact', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $messageCount = random_int(2, 5);

        // Create chat history and add messages
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        for ($i = 0; $i < $messageCount; $i++) {
            $content = fake()->paragraph();
            $message = $i % 2 === 0
                ? new UserMessage($content)
                : new AssistantMessage($content);

            $chatHistory->addMessage($message);
        }

        // Create new chat history instance to test retrieval
        $newChatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Property: All message attributes should be preserved
        $retrievedMessages = $newChatHistory->getMessages();

        expect($retrievedMessages)->toHaveCount($messageCount);

        foreach ($retrievedMessages as $message) {
            expect($message->getRole())->toBeIn(['user', 'assistant']);
            expect($message->getContent())->toBeString();
            expect($message->getContent())->not->toBeEmpty();
        }
    })->repeat(100);

    it('retrieves messages with special characters and unicode intact', function () {
        // Generate random test data with special characters
        $threadId = 'thread_'.uniqid();
        $specialContents = [
            '🏇 Training advice for 🎯 speed!',
            'ウマ娘のトレーニング戦略',
            'Training 🏃‍♀️ for ウマ娘 character!',
            'Stats: Speed↑ Stamina↓ Power→',
            "She said: \"Let's train!\" and I agreed.",
        ];

        // Create chat history and add messages
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        foreach ($specialContents as $content) {
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);
        }

        // Create new chat history instance to test retrieval
        $newChatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Property: Special characters should be preserved during retrieval
        $retrievedMessages = $newChatHistory->getMessages();

        expect($retrievedMessages)->toHaveCount(count($specialContents));

        foreach ($retrievedMessages as $index => $retrievedMessage) {
            expect($retrievedMessage->getContent())->toBe($specialContents[$index]);
        }
    })->repeat(50);

    it('retrieves messages after adding new ones to existing thread', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $initialMessageCount = random_int(2, 5);
        $additionalMessageCount = random_int(2, 5);

        // Create chat history and add initial messages
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        for ($i = 0; $i < $initialMessageCount; $i++) {
            $message = new UserMessage("Initial message {$i}");
            $chatHistory->addMessage($message);
        }

        // Add more messages to the same thread
        for ($i = 0; $i < $additionalMessageCount; $i++) {
            $message = new AssistantMessage("Additional message {$i}");
            $chatHistory->addMessage($message);
        }

        // Create new chat history instance to test retrieval
        $newChatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Property: Should retrieve all messages including newly added ones
        $retrievedMessages = $newChatHistory->getMessages();

        $totalMessages = $initialMessageCount + $additionalMessageCount;
        expect($retrievedMessages)->toHaveCount($totalMessages);

        // Verify initial messages
        for ($i = 0; $i < $initialMessageCount; $i++) {
            expect($retrievedMessages[$i]->getContent())->toBe("Initial message {$i}");
            expect($retrievedMessages[$i]->getRole())->toBe('user');
        }

        // Verify additional messages
        for ($i = 0; $i < $additionalMessageCount; $i++) {
            $index = $initialMessageCount + $i;
            expect($retrievedMessages[$index]->getContent())->toBe("Additional message {$i}");
            expect($retrievedMessages[$index]->getRole())->toBe('assistant');
        }
    })->repeat(100);

    it('retrieves messages with varying content lengths', function () {
        // Generate random test data with varying content lengths
        $threadId = 'thread_'.uniqid();
        $contentLengths = [
            'short' => fake()->word(),
            'medium' => fake()->sentence(),
            'long' => fake()->paragraph(5),
            'very_long' => fake()->text(1000),
        ];

        // Create chat history and add messages
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        foreach ($contentLengths as $content) {
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);
        }

        // Create new chat history instance to test retrieval
        $newChatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Property: Messages of all lengths should be retrievable
        $retrievedMessages = $newChatHistory->getMessages();

        expect($retrievedMessages)->toHaveCount(count($contentLengths));

        foreach ($retrievedMessages as $index => $retrievedMessage) {
            $expectedContent = array_values($contentLengths)[$index];
            expect($retrievedMessage->getContent())->toBe($expectedContent);
        }
    })->repeat(50);

    it('retrieves messages consistently across multiple retrieval attempts', function () {
        // Generate random test data
        $threadId = 'thread_'.uniqid();
        $messageCount = random_int(3, 7);

        // Create chat history and add messages
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        for ($i = 0; $i < $messageCount; $i++) {
            $content = "Message {$i}: ".fake()->sentence();
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);
        }

        // Property: Multiple retrievals should return identical results
        $retrieval1 = (new EloquentChatHistory($threadId, ChatMessage::class))->getMessages();
        $retrieval2 = (new EloquentChatHistory($threadId, ChatMessage::class))->getMessages();
        $retrieval3 = (new EloquentChatHistory($threadId, ChatMessage::class))->getMessages();

        expect($retrieval1)->toHaveCount($messageCount);
        expect($retrieval2)->toHaveCount($messageCount);
        expect($retrieval3)->toHaveCount($messageCount);

        // Verify all retrievals have identical content
        for ($i = 0; $i < $messageCount; $i++) {
            expect($retrieval1[$i]->getContent())->toBe($retrieval2[$i]->getContent());
            expect($retrieval2[$i]->getContent())->toBe($retrieval3[$i]->getContent());
            expect($retrieval1[$i]->getRole())->toBe($retrieval2[$i]->getRole());
            expect($retrieval2[$i]->getRole())->toBe($retrieval3[$i]->getRole());
        }
    })->repeat(100);

    it('retrieves messages with numeric content correctly', function () {
        // Generate random test data with numeric content
        $threadId = 'thread_'.uniqid();
        $numericContents = [
            (string) random_int(1, 1000),
            (string) (random_int(1, 100) / 10),
            '0',
            '-'.random_int(1, 100),
        ];

        // Create chat history and add messages
        $chatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        foreach ($numericContents as $content) {
            $message = new UserMessage($content);
            $chatHistory->addMessage($message);
        }

        // Create new chat history instance to test retrieval
        $newChatHistory = new EloquentChatHistory(
            threadId: $threadId,
            modelClass: ChatMessage::class
        );

        // Property: Numeric content should be retrieved as strings
        $retrievedMessages = $newChatHistory->getMessages();

        expect($retrievedMessages)->toHaveCount(count($numericContents));

        foreach ($retrievedMessages as $index => $retrievedMessage) {
            expect($retrievedMessage->getContent())->toBe($numericContents[$index]);
        }
    })->repeat(50);
});
