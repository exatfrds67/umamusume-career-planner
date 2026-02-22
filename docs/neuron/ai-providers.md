# AI Providers

Interact with LLM providers or extend the framework to implement new ones.

## Overview

With Neuron you can switch between LLM providers with just one line of code, without any impact on your agent
implementation. All providers implement the `AIProviderInterface`, ensuring consistent behavior across different LLM
services.

## Supported Providers

### Anthropic

```php
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\HttpClientOptions;

protected function provider(): AIProviderInterface
{
    return new Anthropic(
        key: 'ANTHROPIC_API_KEY',
        model: 'claude-3-5-sonnet-20241022',
        parameters: [], // Custom params (temperature, etc)
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```text

### OpenAI (Responses API)

Uses the most recent OpenAI responses API:

```php
use NeuronAI\Providers\OpenAI\Responses\OpenAIResponses;

protected function provider(): AIProviderInterface
{
    return new OpenAIResponses(
        key: 'OPENAI_API_KEY',
        model: 'gpt-4',
        parameters: [],
        strict_response: false, // Strict structured output
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```

### OpenAI (Legacy Completions API)

```php
use NeuronAI\Providers\OpenAI\OpenAI;

protected function provider(): AIProviderInterface
{
    return new OpenAI(
        key: 'OPENAI_API_KEY',
        model: 'gpt-4',
        parameters: [],
        strict_response: false,
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```text

### Azure OpenAI

Connect with OpenAI models provided in the Azure cloud platform:

```php
use NeuronAI\Providers\AzureOpenAI;

protected function provider(): AIProviderInterface
{
    return new AzureOpenAI(
        key: 'AZURE_API_KEY',
        endpoint: 'AZURE_ENDPOINT',
        model: 'OPENAI_MODEL',
        version: 'AZURE_API_VERSION'
    );
}
```

### OpenAI-Like Providers

Simplifies connection with providers offering the same data format as OpenAI:

```php
use NeuronAI\Providers\OpenAILike;

protected function provider(): AIProviderInterface
{
    return new OpenAILike(
        baseUri: 'https://api.together.xyz/v1',
        key: 'API_KEY',
        model: 'MODEL',
        parameters: [],
        strict_response: false,
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```text

### Ollama (Local Models)

```php
use NeuronAI\Providers\Ollama\Ollama;

protected function provider(): AIProviderInterface
{
    return new Ollama(
        url: 'http://localhost:11434',
        model: 'llama2',
        parameters: [],
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```

### Google Gemini

```php
use NeuronAI\Providers\Gemini\Gemini;

protected function provider(): AIProviderInterface
{
    return new Gemini(
        key: 'GEMINI_API_KEY',
        model: 'gemini-pro',
        parameters: [],
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```text

### Gemini Vertex AI

Requires the `google/auth` package:

```bash
composer require google/auth
```

```php
use NeuronAI\Providers\Gemini\GeminiVertex;

protected function provider(): AIProviderInterface
{
    return new GeminiVertex(
        pathJsonCredentials: 'GOOGLE_FILE_CREDENTIALS_PATH',
        location: 'us-central1',
        projectId: 'GOOGLE_PROJECT_ID',
        model: 'gemini-pro',
        parameters: [],
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```text

### Mistral

```php
use NeuronAI\Providers\Mistral\Mistral;

protected function provider(): AIProviderInterface
{
    return new Mistral(
        key: 'MISTRAL_API_KEY',
        model: 'mistral-large-latest',
        parameters: [],
        strict_response: false,
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```

### HuggingFace

```php
use NeuronAI\Providers\HuggingFace\HuggingFace;
use NeuronAI\Providers\HuggingFace\InferenceProvider;

protected function provider(): AIProviderInterface
{
    return new HuggingFace(
        key: 'HF_ACCESS_TOKEN',
        model: 'mistralai/Mistral-7B-Instruct-v0.3',
        inferenceProvider: InferenceProvider::HF_INFERENCE,
        parameters: [
            'max_tokens' => 500,
            'temperature' => 0.5
        ]
    );
}
```text

### Deepseek

```php
use NeuronAI\Providers\Deepseek\Deepseek;

protected function provider(): AIProviderInterface
{
    return new Deepseek(
        key: 'DEEPSEEK_API_KEY',
        model: 'deepseek-chat',
        parameters: [],
        strict_response: false,
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```

### Grok (X.AI)

```php
use NeuronAI\Providers\XAI\Grok;

protected function provider(): AIProviderInterface
{
    return new Grok(
        key: 'GROK_API_KEY',
        model: 'grok-beta',
        parameters: [],
        strict_response: false,
        httpOptions: new HttpClientOptions(timeout: 30),
    );
}
```text

### AWS Bedrock Runtime

Requires the AWS SDK:

```bash
composer require aws/aws-sdk-php
```

```php
use Aws\BedrockRuntime\BedrockRuntimeClient;
use NeuronAI\Providers\AWS\BedrockRuntime;

protected function provider(): AIProviderInterface
{
    $client = new BedrockRuntimeClient([
        'version' => 'latest',
        'region' => 'us-east-1',
        'credentials' => [
            'key' => 'AWS_BEDROCK_KEY',
            'secret' => 'AWS_BEDROCK_SECRET',
        ],
    ]);
    
    return new BedrockRuntime(
        client: $client,
        model: 'anthropic.claude-v2',
        inferenceConfig: []
    );
}
```text

## Custom HTTP Options

Providers use an HTTP client to communicate with remote services. Customize the HTTP client configuration:

```php
use NeuronAI\Providers\HttpClientOptions;

protected function provider(): AIProviderInterface
{
    return new Ollama(
        url: 'OLLAMA_URL',
        model: 'OLLAMA_MODEL',
        httpOptions: new HttpClientOptions(
            timeout: 30,
            connect_timeout: 10,
            headers: ['Custom-Header' => 'value']
        )
    );
}
```

`HttpClientOptions` allows customization of:

- `timeout`: Request timeout in seconds
- `connect_timeout`: Connection timeout in seconds
- `headers`: Additional HTTP headers

## Implementing a Custom Provider

To create a new provider, implement the `AIProviderInterface`:

```php
namespace NeuronAI\Providers;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Tools\ToolInterface;

interface AIProviderInterface
{
    /**
     * Send predefined instruction to the LLM.
     */
    public function systemPrompt(?string $prompt): AIProviderInterface;

    /**
     * Set the tools to be exposed to the LLM.
     *
     * @param array<ToolInterface> $tools
     */
    public function setTools(array $tools): AIProviderInterface;
    
    /**
     * The component responsible for mapping NeuronAI Message to provider format.
     */
    public function messageMapper(): MessageMapperInterface;

    /**
     * Send a prompt to the AI agent.
     */
    public function chat(array $messages): Message;
    
    /**
     * Yield the LLM response.
     */
    public function stream(array|string $messages, callable $executeToolsCallback): \Generator;
    
    /**
     * Schema validated response.
     */
    public function structured(string $class, Message|array $messages, int $maxRetry = 1): mixed;
}
```text

### Basic Template

```php
namespace App\Neuron\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\HandleWithTools;
use NeuronAI\Providers\MessageMapperInterface;

class MyAIProvider implements AIProviderInterface
{
    use HandleWithTools;
    
    protected Client $client;
    protected string $system;
    protected MessageMapperInterface $messageMapper;
    
    public function __construct(
        protected string $key,
        protected string $model
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.provider.com/v1',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->key}",
            ]
        ]);
    }

    public function systemPrompt(string $prompt): AIProviderInterface
    {
        $this->system = $prompt;
        return $this;
    }

    public function messageMapper(): MessageMapperInterface
    {
        return $this->messageMapper ?? $this->messageMapper = new MessageMapper();
    }

    public function chat(array $messages): Message
    {
        $result = $this->client->post('chat', [
            RequestOptions::JSON => [
                'model' => $this->model,
                'messages' => \array_map(function (Message $message) {
                    return $message->jsonSerialize();
                }, $messages)
            ]
        ])->getBody()->getContents();
        
        $result = \json_decode($result, true);

        return new AssistantMessage($result['content']);
    }
}
```

### Contributing

We strongly recommend submitting new provider implementations via PR on the official repository or using [Inspector.dev](https://inspector.dev) support channels. Community contributions receive important advancement support.

---

**Source:** <https://docs.neuron-ai.dev/components/ai-provider>
