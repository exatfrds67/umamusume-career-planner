<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Vector Store Service for RAG (Retrieval-Augmented Generation)
 *
 * Handles document embedding, storage, and similarity search for knowledge base retrieval.
 * Uses OpenAI embeddings with local caching to minimize API costs.
 */
class VectorStoreService
{
    private const CACHE_TTL = 86400; // 24 hours

    private const EMBEDDING_MODEL = 'text-embedding-3-small';

    // Embedding dimensions for the model
    /** @phpstan-ignore classConstant.unused */
    private const EMBEDDING_DIMENSIONS = 1536;

    private const SIMILARITY_THRESHOLD = 0.7;

    private readonly string $apiKey;

    public function __construct(
        private readonly string $knowledgeBasePath = 'storage/knowledge-base',
        ?string $openaiKey = null
    ) {
        $configKey = config('services.openai.key');
        $this->apiKey = $openaiKey ?? (is_string($configKey) ? $configKey : '');
    }

    /**
     * Load and process knowledge base documents
     *
     * @return array<int, array{content: string, metadata: array<string, string>, embedding: array<int, float>|null}>
     */
    public function loadKnowledgeBase(string $category = 'game-mechanics'): array
    {
        $cacheKey = "knowledge_base_{$category}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category) {
            $documents = [];
            $basePath = base_path($this->knowledgeBasePath.'/'.$category);

            if (! File::exists($basePath)) {
                Log::warning("Knowledge base path not found: {$basePath}");

                return [];
            }

            $files = File::files($basePath);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'md') {
                    continue;
                }

                $content = File::get($file->getPathname());
                $chunks = $this->chunkDocument($content, $file->getFilename());

                foreach ($chunks as $chunk) {
                    $documents[] = $chunk;
                }
            }

            return $documents;
        });
    }

    /**
     * Chunk document into smaller segments for embedding
     *
     * @return array<int, array{content: string, metadata: array<string, string>, embedding: null}>
     */
    private function chunkDocument(string $content, string $filename): array
    {
        $chunks = [];
        $lines = explode("\n", $content);
        $currentChunk = [];
        $currentSize = 0;
        $maxChunkSize = 1000; // characters
        $overlap = 200; // overlap between chunks

        $metadata = [
            'source' => $filename,
            'category' => 'game-mechanics',
        ];

        foreach ($lines as $line) {
            $lineSize = strlen($line);

            if ($currentSize + $lineSize > $maxChunkSize && count($currentChunk) > 0) {
                // Save current chunk
                $chunks[] = [
                    'content' => implode("\n", $currentChunk),
                    'metadata' => $metadata,
                    'embedding' => null, // Will be generated on demand
                ];

                // Keep last few lines for overlap
                $overlapLines = array_slice($currentChunk, -5);
                $currentChunk = $overlapLines;
                $currentSize = array_sum(array_map('strlen', $overlapLines));
            }

            $currentChunk[] = $line;
            $currentSize += $lineSize;
        }

        // Add final chunk
        if (count($currentChunk) > 0) {
            $chunks[] = [
                'content' => implode("\n", $currentChunk),
                'metadata' => $metadata,
                'embedding' => null,
            ];
        }

        return $chunks;
    }

    /**
     * Generate embedding for text using OpenAI API
     *
     * @return array<int, float>|null
     */
    public function generateEmbedding(string $text): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key not configured for embeddings');

            return null;
        }

        $cacheKey = 'embedding_'.md5($text);

        /** @var array<int, float>|null $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL * 7, function () use ($text) {
            try {
                $timeoutConfig = config('services.openai.timeout', 30);
                $timeout = is_int($timeoutConfig) || is_float($timeoutConfig)
                    ? $timeoutConfig
                    : (is_numeric($timeoutConfig) ? (float) $timeoutConfig : 30);

                $response = Http::timeout($timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$this->apiKey,
                        'Content-Type' => 'application/json',
                    ])->post('https://api.openai.com/v1/embeddings', [
                        'model' => self::EMBEDDING_MODEL,
                        'input' => $text,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
                        $firstItem = $data['data'][0] ?? null;
                        if (is_array($firstItem) && isset($firstItem['embedding']) && is_array($firstItem['embedding'])) {
                            return $firstItem['embedding'];
                        }
                    }

                    return null;
                }

                Log::error('OpenAI embedding API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Exception generating embedding', [
                    'message' => $e->getMessage(),
                ]);

                return null;
            }
        });

        return $result;
    }

    /**
     * Calculate cosine similarity between two vectors
     *
     * @param  array<int, float>  $vecA
     * @param  array<int, float>  $vecB
     */
    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        if (count($vecA) !== count($vecB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        for ($i = 0; $i < count($vecA); $i++) {
            $dotProduct += $vecA[$i] * $vecB[$i];
            $magnitudeA += $vecA[$i] * $vecA[$i];
            $magnitudeB += $vecB[$i] * $vecB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Search knowledge base for relevant documents
     *
     * @return array<int, array{content: string, metadata: array<string, string>, similarity: float}>
     */
    public function searchSimilar(string $query, int $topK = 3, ?string $category = null): array
    {
        $queryEmbedding = $this->generateEmbedding($query);

        if ($queryEmbedding === null) {
            Log::warning('Failed to generate query embedding, falling back to keyword search');

            // Convert keyword search results to match expected format
            $keywordResults = $this->keywordSearch($query, $topK, $category);

            return array_map(function ($result) {
                return [
                    'content' => $result['content'],
                    'metadata' => $result['metadata'],
                    'similarity' => (float) $result['score'] / 100, // Normalize score to 0-1 range
                ];
            }, $keywordResults);
        }

        $documents = $category ? $this->loadKnowledgeBase($category) : $this->loadAllDocuments();
        $results = [];

        foreach ($documents as $doc) {
            // Generate embedding for document if not cached
            if ($doc['embedding'] === null) {
                $doc['embedding'] = $this->generateEmbedding($doc['content']);
            }

            if ($doc['embedding'] === null) {
                continue;
            }

            $similarity = $this->cosineSimilarity($queryEmbedding, $doc['embedding']);

            if ($similarity >= self::SIMILARITY_THRESHOLD) {
                $results[] = [
                    'content' => $doc['content'],
                    'metadata' => $doc['metadata'],
                    'similarity' => $similarity,
                ];
            }
        }

        // Sort by similarity (highest first)
        usort($results, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($results, 0, $topK);
    }

    /**
     * Fallback keyword-based search when embeddings unavailable
     *
     * @return array<int, array{content: string, metadata: array<string, string>, score: int}>
     */
    private function keywordSearch(string $query, int $topK = 3, ?string $category = null): array
    {
        $documents = $category ? $this->loadKnowledgeBase($category) : $this->loadAllDocuments();
        $keywords = array_filter(explode(' ', strtolower($query)));
        $results = [];

        foreach ($documents as $doc) {
            $content = strtolower($doc['content']);
            $score = 0;

            foreach ($keywords as $keyword) {
                $score += substr_count($content, $keyword);
            }

            if ($score > 0) {
                $results[] = [
                    'content' => $doc['content'],
                    'metadata' => $doc['metadata'],
                    'score' => $score,
                ];
            }
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, $topK);
    }

    /**
     * Load all documents from all categories
     *
     * @return array<int, array{content: string, metadata: array<string, string>, embedding: array<int, float>|null}>
     */
    private function loadAllDocuments(): array
    {
        $basePath = base_path($this->knowledgeBasePath);
        $allDocuments = [];

        if (! File::exists($basePath)) {
            return [];
        }

        $directories = File::directories($basePath);

        foreach ($directories as $dir) {
            $category = basename($dir);
            $documents = $this->loadKnowledgeBase($category);
            $allDocuments = array_merge($allDocuments, $documents);
        }

        return $allDocuments;
    }

    /**
     * Retrieve relevant context for a query
     *
     * @param  array<string, mixed>  $options
     */
    public function getRelevantContext(string $query, array $options = []): string
    {
        $topK = isset($options['top_k']) && is_int($options['top_k']) ? $options['top_k'] : 3;
        $category = isset($options['category']) && is_string($options['category']) ? $options['category'] : null;
        $includeMetadata = $options['include_metadata'] ?? false;

        $results = $this->searchSimilar($query, $topK, $category);

        if (empty($results)) {
            return '';
        }

        $context = "**Relevant Game Knowledge:**\n\n";

        foreach ($results as $index => $result) {
            $context .= '### Source '.($index + 1);

            if ($includeMetadata && isset($result['metadata']['source'])) {
                $context .= " ({$result['metadata']['source']})";
            }

            $context .= "\n\n";
            $context .= trim($result['content'])."\n\n";
        }

        return $context;
    }

    /**
     * Clear all cached embeddings and knowledge base
     */
    public function clearCache(): void
    {
        $categories = ['game-mechanics', 'strategies'];

        foreach ($categories as $category) {
            Cache::forget("knowledge_base_{$category}");
        }

        // Note: Individual embedding cache keys remain for 7 days
        // Full cache clear would require pattern matching which is store-dependent

        Log::info('Knowledge base cache cleared');
    }
}
