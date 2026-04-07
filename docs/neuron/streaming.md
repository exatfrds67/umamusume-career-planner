# Streaming

Presenting AI response to your user in real-time.

## Overview

Streaming enables you to show users chunks of response text as they arrive rather than waiting for the full response.
You can offer a real-time Agent conversation experience.

## Using Stream Method

To stream the AI response, use the `stream()` method instead of `chat()`. This method returns a PHP generator that can
be used to process the response as an iterable object.

```php
use NeuronAI\Chat\Messages\UserMessage;

$stream = MyAgent::make()->stream(
    new UserMessage("Tell me a story")
);

foreach ($stream as $chunk) {
    echo $chunk;
    flush(); // Send to browser immediately
}
```text

## Streaming with Tools

Neuron supports Tools & Function calls in combination with streaming responses. You are free to provide your Agents with
Tools and they will be automatically handled in the middle of the stream to continue toward the final response.

The framework automatically manages:

- Tool execution during streaming
- Resuming the stream after tool completion
- Maintaining conversation context

```php
protected function tools(): array
{
    return [
        Tool::make('get_weather', 'Get current weather')
            ->addProperty(new ToolProperty(
                name: 'city',
                type: PropertyType::STRING,
                description: 'City name',
                required: true
            ))
            ->setCallable(function (string $city) {
                return "Weather in {$city}: Sunny, 72°F";
            })
    ];
}

// Stream will pause for tool execution, then continue
$stream = MyAgent::make()->stream(
    new UserMessage("What's the weather in Paris?")
);

foreach ($stream as $chunk) {
    echo $chunk;
}
```text

## Monitoring Streaming

To watch inside this workflow, connect your Agent to the [Inspector monitoring dashboard](https://inspector.dev) to see
the tool call execution flow in real-time.

After you sign up, set the `INSPECTOR_INGESTION_KEY` variable in your environment file:

```env
INSPECTOR_INGESTION_KEY=your_key_here
```text

## Use Cases

Streaming is particularly useful for:

- **Long-form content generation**: Stories, articles, reports
- **Interactive chat interfaces**: Real-time conversation feel
- **Progressive disclosure**: Show results as they become available
- **User engagement**: Keep users engaged during processing

## Implementation Tips

1. **Flush output buffers**: Use `flush()` to send chunks immediately to the browser
2. **Handle errors gracefully**: Wrap streaming in try-catch blocks
3. **Show loading indicators**: Display status while waiting for first chunk
4. **Consider timeouts**: Set appropriate timeout values for long-running streams

---

**Source:** <https://docs.neuron-ai.dev/getting-started/streaming>
