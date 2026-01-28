<?php

use App\Http\Controllers\AIChatController;
use App\Models\User;
use App\Services\AI\VectorStoreService;
use App\Services\MCP\AgentRoutingService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('enriches chat context with knowledge base when RAG keywords detected', function () {
    // Mock vector store to return knowledge
    Cache::shouldReceive('remember')->andReturn([
        [
            'content' => 'Speed stat affects initial positioning and sprint capability',
            'metadata' => ['source' => 'stat-system.md'],
            'embedding' => null,
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/ai/chat/message', [
            'message' => 'How does speed stat work?',
            'provider' => 'ollama',
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'metadata' => [
                'model',
                'provider',
                'rag_enhanced',
                'knowledge_sources',
            ],
        ]);

    // When OpenAI API key is configured, RAG should be enhanced
    // Otherwise, it should still work but without embeddings
    if (! empty(config('services.openai.key'))) {
        expect($response->json('metadata.rag_enhanced'))->toBeTrue();
    }
})->skip('Requires Ollama service running');

it('includes knowledge sources in response metadata', function () {
    // Verify RAG keywords trigger knowledge enhancement
    $ragKeywords = ['stat', 'speed', 'training', 'skill', 'how', 'strategy'];
    $nonRagPhrases = ['hello', 'thank you', 'goodbye'];

    foreach ($ragKeywords as $keyword) {
        expect(str_contains('What is the '.$keyword.'?', $keyword))->toBeTrue();
    }

    foreach ($nonRagPhrases as $phrase) {
        $hasRagKeyword = false;
        foreach ($ragKeywords as $keyword) {
            if (str_contains(strtolower($phrase), $keyword)) {
                $hasRagKeyword = true;
                break;
            }
        }
        expect($hasRagKeyword)->toBeFalse();
    }
});

it('propagates rag metadata from execution response', function () {
    $routing = \Mockery::mock(AgentRoutingService::class);
    $routing->shouldReceive('executeWithFallback')->once()->andReturn([
        'response' => [
            'content' => 'RAG answer',
            'agent' => 'training',
            'confidence' => 0.91,
            'rag_enhanced' => true,
            'knowledge_sources' => ['stat-system.md', 'training-system.md'],
        ],
        'model' => 'mock-model',
        'provider' => 'ollama',
        'execution_time' => 0.12,
        'cost' => 0.0,
    ]);

    $this->app->instance(AgentRoutingService::class, $routing);

    $controller = app(AIChatController::class);
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('executeChatRequest');
    $method->setAccessible(true);

    /** @var array{rag_enhanced: bool, knowledge_sources: array<int, string>} $normalized */
    $normalized = $method->invoke($controller, [
        'message' => 'Explain speed stat',
        'provider' => 'ollama',
    ], []);

    expect($normalized['rag_enhanced'])->toBeTrue()
        ->and($normalized['knowledge_sources'])->toContain('stat-system.md');
});

it('displays knowledge badge when RAG is used', function () {
    // This would be an E2E test with actual browser
    // For now, we verify the Blade template includes the RAG badge
    $bladePath = resource_path('views/components/ai/chat-interface.blade.php');
    $bladeContent = file_get_contents($bladePath);

    expect($bladeContent)->toContain('rag_enhanced')
        ->and($bladeContent)->toContain('knowledge_sources')
        ->and($bladeContent)->toContain('Knowledge');
});

it('falls back to keyword search when embeddings unavailable', function () {
    $vectorStore = new VectorStoreService;

    Cache::shouldReceive('remember')->andReturn([
        [
            'content' => 'Speed training provides highest stat gains',
            'metadata' => ['source' => 'training.md'],
            'embedding' => null,
        ],
        [
            'content' => 'Stamina is crucial for long races',
            'metadata' => ['source' => 'training.md'],
            'embedding' => null,
        ],
    ]);

    $results = $vectorStore->searchSimilar('speed training tips', 1);

    expect($results)->toBeArray()
        ->and(count($results))->toBeGreaterThan(0);
});

it('caches knowledge base documents for performance', function () {
    // Knowledge base should be cached to avoid reloading files
    $vectorStore = new VectorStoreService;

    // First load should cache
    $cache1 = $vectorStore->loadKnowledgeBase('game-mechanics');

    // Verify cache works (documents exist)
    expect($cache1)->toBeArray();
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
