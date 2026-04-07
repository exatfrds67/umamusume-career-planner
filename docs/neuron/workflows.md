# Workflows

Build complex multi-agent systems with event-driven orchestration.

## Overview

Workflows provide an event-driven, node-based way to control execution flow in complex AI applications. They enable you
to build sophisticated multi-agent systems with human-in-the-loop capabilities, streaming updates, and resumable
execution.

## What is a Workflow?

A workflow divides your application into **Nodes** triggered by **Events**. Nodes can be anything from a single line of
code to a complex agent. By combining nodes and events, you create maintainable flows that encapsulate logic clearly.

Think of it as n8n or Zapier, but at the code level with full programmatic control.

## Why Use Workflows?

### Problems Workflows Solve

**Without Workflows:**

- Complex conditional logic becomes brittle
- Manual state management across agents
- Difficult to pause and resume processes
- Hard to debug multi-step processes
- No streaming updates to clients

**With Workflows:**

- Clear, maintainable process structure
- Automatic state management
- Pause, wait for input, and resume
- Built-in debugging with Inspector
- Real-time streaming to clients

### Key Capabilities

1. **Human-in-the-Loop**: Pause for human review and approval
2. **Streaming**: Send real-time updates during execution
3. **Resumable**: Stop and resume hours or days later
4. **Debuggable**: See exactly what happens at each node
5. **Flexible**: Use any Neuron component (agents, RAG, tools, etc.)

## When to Use Workflows

Use workflows when you need:

- Multiple agents collaborating
- Human approval at critical steps
- Long-running processes with checkpoints
- Complex branching logic
- Real-time progress updates
- Processes that span multiple sessions

For simple single-agent tasks, use the `Agent` or `RAG` classes directly.

## Basic Workflow Structure

```php
use NeuronAI\Workflow\Workflow;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Event;

class MyWorkflow extends Workflow
{
    public function nodes(): array
    {
        return [
            Node::make('start')
                ->handle(function (Event $event) {
                    // Initial processing
                    return new Event('process', ['data' => $result]);
                }),

            Node::make('process')
                ->handle(function (Event $event) {
                    // Main processing
                    $data = $event->getData('data');
                    return new Event('complete', ['result' => $processed]);
                }),

            Node::make('complete')
                ->handle(function (Event $event) {
                    // Finalization
                    return $event->getData('result');
                }),
        ];
    }
}
```text

## Nodes

Nodes are the building blocks of workflows. Each node:

- Has a unique name
- Receives an Event as input
- Returns an Event to trigger the next node

```php
Node::make('analyze_document')
    ->handle(function (Event $event) {
        $document = $event->getData('document');

        // Use an agent within the workflow
        $analysis = AnalyzerAgent::make()->chat(
            new UserMessage("Analyze: {$document}")
        );

        return new Event('review', [
            'document' => $document,
            'analysis' => $analysis->getContent()
        ]);
    })
```text

## Events

Events carry data between nodes and trigger execution:

```php
// Create an event
$event = new Event('node_name', [
    'key' => 'value',
    'data' => $someData
]);

// Access event data
$value = $event->getData('key');
$allData = $event->getAllData();
```text

## Human-in-the-Loop

Pause workflow for human review:

```php
Node::make('review')
    ->handle(function (Event $event) {
        $analysis = $event->getData('analysis');

        // Pause and wait for human approval
        return $this->interrupt('approval_needed', [
            'analysis' => $analysis,
            'message' => 'Please review and approve'
        ]);
    })

Node::make('after_approval')
    ->handle(function (Event $event) {
        $approved = $event->getData('approved');

        if ($approved) {
            return new Event('proceed', $event->getAllData());
        }

        return new Event('reject', $event->getAllData());
    })
```

Resume the workflow after human input:

```php
$workflow = MyWorkflow::make();

// Start workflow
$state = $workflow->run(new Event('start', ['data' => $input]));

// Later, after human review...
$state = $workflow->resume([
    'approved' => true,
    'comments' => 'Looks good'
]);
```text

## Streaming Updates

Send real-time updates to clients:

```php
Node::make('process')
    ->handle(function (Event $event) {
        // Stream progress updates
        $this->stream('Processing step 1...');

        // Do work
        $result1 = $this->processStep1();

        $this->stream('Processing step 2...');
        $result2 = $this->processStep2();

        $this->stream('Complete!');

        return new Event('complete', ['results' => [$result1, $result2]]);
    })
```text

## Using Agents in Workflows

Workflows can use any Neuron component:

```php
Node::make('research')
    ->handle(function (Event $event) {
        $topic = $event->getData('topic');

        // Use RAG agent
        $research = ResearchRAG::make()->chat(
            new UserMessage("Research: {$topic}")
        );

        return new Event('summarize', [
            'research' => $research->getContent()
        ]);
    })

Node::make('summarize')
    ->handle(function (Event $event) {
        $research = $event->getData('research');

        // Use different agent for summarization
        $summary = SummaryAgent::make()->chat(
            new UserMessage("Summarize: {$research}")
        );

        return new Event('complete', [
            'summary' => $summary->getContent()
        ]);
    })
```text

## Conditional Branching

Route execution based on conditions:

```php
Node::make('classify')
    ->handle(function (Event $event) {
        $text = $event->getData('text');
        $category = $this->classifyText($text);

        // Branch based on category
        return match($category) {
            'urgent' => new Event('urgent_handler', $event->getAllData()),
            'normal' => new Event('normal_handler', $event->getAllData()),
            'low' => new Event('low_priority', $event->getAllData()),
        };
    })
```

## Loops and Iterations

Implement iterative processes:

```php
Node::make('iterate')
    ->handle(function (Event $event) {
        $items = $event->getData('items');
        $processed = $event->getData('processed', []);

        if (empty($items)) {
            return new Event('complete', ['results' => $processed]);
        }

        $current = array_shift($items);
        $result = $this->processItem($current);
        $processed[] = $result;

        // Loop back to process next item
        return new Event('iterate', [
            'items' => $items,
            'processed' => $processed
        ]);
    })
```text

## Monitoring Workflows

Enable Inspector monitoring to visualize workflow execution:

```env
INSPECTOR_INGESTION_KEY=your_key_here
```text

Inspector shows:

- Node execution order
- Data passed between nodes
- Execution time per node
- Where workflow paused
- Human approval points
- Errors and exceptions

## Best Practices

1. **Keep nodes focused**: Each node should have a single responsibility
2. **Use descriptive names**: Node and event names should be self-explanatory
3. **Handle errors**: Wrap node logic in try-catch blocks
4. **Monitor execution**: Use Inspector from the start
5. **Test incrementally**: Build and test nodes one at a time
6. **Document flow**: Comment complex branching logic

## Example: Document Processing Workflow

```php
class DocumentWorkflow extends Workflow
{
    public function nodes(): array
    {
        return [
            Node::make('upload')
                ->handle(fn(Event $e) => new Event('extract', $e->getAllData())),

            Node::make('extract')
                ->handle(function (Event $event) {
                    $file = $event->getData('file');
                    $text = $this->extractText($file);
                    return new Event('analyze', ['text' => $text]);
                }),

            Node::make('analyze')
                ->handle(function (Event $event) {
                    $analysis = AnalyzerAgent::make()->chat(
                        new UserMessage($event->getData('text'))
                    );
                    return $this->interrupt('review', [
                        'analysis' => $analysis->getContent()
                    ]);
                }),

            Node::make('approved')
                ->handle(function (Event $event) {
                    $this->saveToDatabase($event->getAllData());
                    return new Event('complete', ['status' => 'success']);
                }),
        ];
    }
}
```text

---

**Source:** <https://docs.neuron-ai.dev/workflow/getting-started>
