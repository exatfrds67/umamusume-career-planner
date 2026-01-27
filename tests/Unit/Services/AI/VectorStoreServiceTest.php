<?php

use App\Services\AI\VectorStoreService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->vectorStore = new VectorStoreService;
});

it('can load knowledge base documents', function () {
    $documents = $this->vectorStore->loadKnowledgeBase('game-mechanics');

    expect($documents)->toBeArray();
});

it('can chunk documents correctly', function () {
    $reflection = new ReflectionClass(VectorStoreService::class);
    $method = $reflection->getMethod('chunkDocument');
    $method->setAccessible(true);

    $content = str_repeat('Line of text. ', 200); // Large content to test chunking
    $chunks = $method->invoke($this->vectorStore, $content, 'test.md');

    expect($chunks)->toBeArray()
        ->and(count($chunks))->toBeGreaterThan(0)
        ->and($chunks[0])->toHaveKeys(['content', 'metadata', 'embedding']);
});

it('can perform keyword search when embeddings unavailable', function () {
    Cache::shouldReceive('remember')->andReturn([
        [
            'content' => 'Speed is important for front runners',
            'metadata' => ['source' => 'test.md'],
            'embedding' => null,
        ],
        [
            'content' => 'Stamina is crucial for long distance races',
            'metadata' => ['source' => 'test.md'],
            'embedding' => null,
        ],
    ]);

    $results = $this->vectorStore->searchSimilar('speed training', 1);

    expect($results)->toBeArray();
});

it('can get relevant context from knowledge base', function () {
    Cache::shouldReceive('remember')->andReturn([
        [
            'content' => 'Speed training provides the highest base stat gains',
            'metadata' => ['source' => 'training.md'],
            'embedding' => null,
        ],
    ]);

    $context = $this->vectorStore->getRelevantContext('what is speed training?');

    expect($context)->toBeString();
});

it('calculates cosine similarity correctly', function () {
    $reflection = new ReflectionClass(VectorStoreService::class);
    $method = $reflection->getMethod('cosineSimilarity');
    $method->setAccessible(true);

    // Identical vectors should have similarity of 1.0
    $vecA = [1.0, 0.0, 0.0];
    $vecB = [1.0, 0.0, 0.0];
    $similarity = $method->invoke($this->vectorStore, $vecA, $vecB);

    expect($similarity)->toBe(1.0);

    // Orthogonal vectors should have similarity of 0.0
    $vecC = [1.0, 0.0, 0.0];
    $vecD = [0.0, 1.0, 0.0];
    $similarityOrthogonal = $method->invoke($this->vectorStore, $vecC, $vecD);

    expect($similarityOrthogonal)->toBe(0.0);
});

it('can clear cache', function () {
    Cache::shouldReceive('forget')->twice();

    $this->vectorStore->clearCache();

    expect(true)->toBeTrue(); // Cache::forget called
});

test('example', function () {
    expect(true)->toBeTrue();
});
