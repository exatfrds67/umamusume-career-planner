# RAG (Retrieval-Augmented Generation)

Build AI agents that can access and reason over your private data.

## Overview

Retrieval-Augmented Generation (RAG) extends LLM capabilities to work with your organization's internal knowledge base
without retraining the model. RAG systems retrieve relevant information from external sources and provide it as context
to the LLM for generating accurate, informed responses.

## Why RAG?

**Without RAG:** LLM responds based only on training data
**With RAG:** LLM retrieves relevant information from your data sources first, then generates responses using both
retrieved context and training data

### Use Cases

- Answer questions about internal documentation
- Customer support chatbots using company policies
- Research assistants with access to latest papers
- Code assistants with access to your codebase

## How RAG Works

### Three Key Steps

1. **Process External Data**: Convert documents into embeddings
2. **Store in Vector Database**: Save embeddings for similarity search
3. **Retrieve & Augment**: Find relevant data and add to LLM context

Neuron automates steps 2 and 3 - you only need to handle data processing.

## Creating a RAG Agent

### Basic RAG Setup

```php
use NeuronAI\RAG\RAG;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\OpenAI\OpenAIEmbeddings;
use NeuronAI\VectorStores\Qdrant\Qdrant;

class DocumentRAG extends RAG
{
    protected function provider(): AIProviderInterface
    {
        return new Anthropic(
            key: 'ANTHROPIC_API_KEY',
            model: 'claude-3-5-sonnet-20241022',
        );
    }

    protected function embeddingsProvider(): EmbeddingsProviderInterface
    {
        return new OpenAIEmbeddings(
            key: 'OPENAI_API_KEY',
            model: 'text-embedding-3-small'
        );
    }

    protected function vectorStore(): VectorStoreInterface
    {
        return new Qdrant(
            url: 'http://localhost:6333',
            collectionName: 'documents',
            size: 1536 // Must match embedding model dimensions
        );
    }

    public function instructions(): string
    {
        return "You are a helpful assistant that answers questions based on the provided documents.";
    }
}
```text

## Using the RAG Agent

```php
use NeuronAI\Chat\Messages\UserMessage;

$response = DocumentRAG::make()->chat(
    new UserMessage("What is the refund policy?")
);

echo $response->getContent();
```

The RAG agent will:

1. Convert the question to an embedding
2. Search the vector store for relevant documents
3. Provide those documents as context to the LLM
4. Generate a response based on the retrieved information

## Loading Data into RAG

Use Data Loaders to populate your vector store:

```php
use NeuronAI\DataLoaders\FileLoader;
use NeuronAI\DataLoaders\TextSplitter;

$rag = DocumentRAG::make();

// Load and process documents
$loader = new FileLoader('path/to/documents');
$splitter = new TextSplitter(chunkSize: 1000, overlap: 200);

$documents = $loader->load();
$chunks = $splitter->split($documents);

// Store in vector database
$rag->loadDocuments($chunks);
```text

## RAG with Tools

RAG agents extend the base `Agent` class, so you can combine retrieval with tools:

```php
class WorkoutRAG extends RAG
{
    protected function provider(): AIProviderInterface
    {
        return new Anthropic(
            key: 'ANTHROPIC_API_KEY',
            model: 'claude-3-5-sonnet-20241022',
        );
    }

    protected function embeddingsProvider(): EmbeddingsProviderInterface
    {
        return new OpenAIEmbeddings(
            key: 'OPENAI_API_KEY',
            model: 'text-embedding-3-small'
        );
    }

    protected function vectorStore(): VectorStoreInterface
    {
        return new Qdrant(
            url: 'http://localhost:6333',
            collectionName: 'workouts',
            size: 1536
        );
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: ["You are a fitness coach providing workout tips."],
            steps: [
                "Retrieve relevant workout information from the knowledge base.",
                "Get the user's current workout status from the database.",
                "Provide personalized tips based on both sources."
            ]
        );
    }

    protected function tools(): array
    {
        return [
            Tool::make('get_user_workout_status', 'Get user workout data')
                ->addProperty(new ToolProperty(
                    name: 'user_id',
                    type: PropertyType::INTEGER,
                    description: 'User ID',
                    required: true
                ))
                ->setCallable(function (int $user_id) {
                    return User::find($user_id)->workouts()->latest()->first();
                })
        ];
    }
}
```

## Advanced RAG Features

### Custom Retrieval Strategies

Implement custom retrieval logic by extending retrieval components.

### Pre/Post Processors

Optimize RAG output with custom processors for:

- Document preprocessing
- Query enhancement
- Response post-processing

### Multiple Vector Stores

Use different vector stores for different data types or access patterns.

## Monitoring RAG Systems

Enable Inspector monitoring to track:

- Retrieval queries
- Documents retrieved
- Embedding generation
- Tool calls
- Final responses

```env
INSPECTOR_INGESTION_KEY=your_key_here
```text

## Best Practices

1. **Chunk size**: Balance between context and relevance (500-1500 tokens)
2. **Overlap**: Use 10-20% overlap between chunks
3. **Metadata**: Include metadata with chunks for filtering
4. **Embedding model**: Match dimensions with vector store configuration
5. **Testing**: Test retrieval quality before deploying

---

**Source:** <https://docs.neuron-ai.dev/rag>

