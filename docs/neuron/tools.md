# Tools & Toolkits

Extend agent capabilities with custom tools and pre-built toolkits.

## Overview

Tools allow your AI agents to interact with external systems, perform calculations, query databases, call APIs, and
execute custom logic. Neuron provides a flexible tool system that integrates seamlessly with agent workflows.

## Creating Custom Tools

Define tools using the `Tool` class with properties and a callable function:

```php
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;

protected function tools(): array
{
    return [
        Tool::make('get_weather', 'Get current weather for a city')
            ->addProperty(new ToolProperty(
                name: 'city',
                type: PropertyType::STRING,
                description: 'City name',
                required: true
            ))
            ->setCallable(function (string $city) {
                // Your implementation
                return "Weather in {$city}: Sunny, 72°F";
            })
    ];
}
```text

## Tool Properties

Define input parameters for your tools:

```php
Tool::make('calculate', 'Perform calculation')
    ->addProperty(new ToolProperty(
        name: 'operation',
        type: PropertyType::STRING,
        description: 'Operation: add, subtract, multiply, divide',
        required: true,
        enum: ['add', 'subtract', 'multiply', 'divide']
    ))
    ->addProperty(new ToolProperty(
        name: 'a',
        type: PropertyType::NUMBER,
        description: 'First number',
        required: true
    ))
    ->addProperty(new ToolProperty(
        name: 'b',
        type: PropertyType::NUMBER,
        description: 'Second number',
        required: true
    ))
    ->setCallable(function (string $operation, float $a, float $b) {
        return match($operation) {
            'add' => $a + $b,
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $b != 0 ? $a / $b : 'Cannot divide by zero',
        };
    });
```text

### Available Property Types

- `PropertyType::STRING` - Text values
- `PropertyType::NUMBER` - Numeric values (int or float)
- `PropertyType::INTEGER` - Integer values only
- `PropertyType::BOOLEAN` - True/false values
- `PropertyType::ARRAY` - Array values
- `PropertyType::OBJECT` - Object/associative array values

## Toolkits

Toolkits are collections of related tools that can be added to agents as a group:

```php
use NeuronAI\Tools\Toolkits\CalculatorToolkit;

protected function tools(): array
{
    return [
        CalculatorToolkit::make(),
    ];
}
```text

## Using Tools in Agents

Tools are automatically invoked by the LLM when needed:

```php
class MathAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new Anthropic(
            key: 'ANTHROPIC_API_KEY',
            model: 'claude-3-5-sonnet-20241022',
        );
    }

    public function instructions(): string
    {
        return "You are a helpful math assistant.";
    }

    protected function tools(): array
    {
        return [
            CalculatorToolkit::make(),
        ];
    }
}

$response = MathAgent::make()->chat(
    new UserMessage("What is 15 * 23?")
);

echo $response->getContent();
// The agent will use the calculator tool and respond with: "345"
```

## Tool Execution Flow

1. User sends a message to the agent
2. LLM determines if a tool is needed
3. LLM requests tool execution with parameters
4. Neuron executes the tool callable
5. Result is sent back to LLM
6. LLM generates final response using tool result

## Complex Tool Examples

### Database Query Tool

```php
Tool::make('query_users', 'Query user database')
    ->addProperty(new ToolProperty(
        name: 'email',
        type: PropertyType::STRING,
        description: 'User email to search',
        required: false
    ))
    ->setCallable(function (?string $email = null) {
        $query = User::query();

        if ($email) {
            $query->where('email', $email);
        }

        return $query->get()->toArray();
    })
```text

### API Call Tool

```php
Tool::make('fetch_github_user', 'Fetch GitHub user information')
    ->addProperty(new ToolProperty(
        name: 'username',
        type: PropertyType::STRING,
        description: 'GitHub username',
        required: true
    ))
    ->setCallable(function (string $username) {
        $response = Http::get("https://api.github.com/users/{$username}");
        return $response->json();
    })
```text

## Best Practices

1. **Clear descriptions**: Provide detailed descriptions for tools and properties
2. **Type safety**: Use appropriate property types
3. **Error handling**: Handle errors gracefully in callables
4. **Return formats**: Return data in formats the LLM can understand (strings, arrays, JSON)
5. **Required vs optional**: Mark properties as required only when necessary

## Monitoring Tool Execution

Connect to Inspector to monitor tool calls in real-time:

```env
INSPECTOR_INGESTION_KEY=your_key_here
```text

This shows:

- Which tools were called
- Parameters passed
- Execution time
- Results returned
- Any errors encountered

---

**Source:** <https://docs.neuron-ai.dev/components/tools>
