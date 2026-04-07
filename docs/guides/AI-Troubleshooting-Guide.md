# AI Chat & API Troubleshooting Guide

This guide covers resolution strategies for AI component and API response handling defects within the Neuron AI Stack.

## JSON Extractor Protocol

**Issue:** Data payloads returned by internal AI components previously differed between the Chat UI
(`data.message`) and side panels (`data.servers`). Laravel enforces global `['success' => true,
'data' => ...]`.

**Resolution:**
The system now enforces defensive unwrapping checks at the integration boundary for Alpine JS
monitoring components (Server Status, Tool Usage, Workflow, Agent Selector):

```javascript
// Automatically unwrap success payloads gracefully
const data = responseData.success !== undefined ? responseData.data : responseData;
```
If errors occur relating to missing array fields (`undefined is not an object`), ensure the
JavaScript boundary uses this ternary evaluation.

## Infinite Fallback Loops on Memory Faults

**Issue:** An OOM error triggered from `ollama` could precipitate an immediate retry cascading down
back to `ollama`, generating infinite recursive application stalling.

**Resolution:**
The `AgentRoutingService` now supports explicit tracking of failure identity. By passing the
`$failedProvider` boundary tag when utilizing `selectFallbackProvider`, the router automatically
bypasses the failing parent and transitions to `bedrock` immediately.
Check `AgentRoutingService::selectFallbackProvider(string $failedProvider = null)` if provider
cascading behaves unexpectedly.

## Actionable Error State UX

**Issue:** The Chat Interface triggered rigid visual failure blocks with no actionable recovery loops.

**Resolution:**
The Blade component `chat-interface.blade.php` supports mapping `msg.actions`. Whenever an SSE event
errors (e.g., rate limits, networking faults, model unavailability), `resources/js/ai-
chat.js::showError()` generates dynamic interactive objects:

- `Network Error`: Exposes **[Retry]** triggering `regenerateResponse()`
- `Rate Limited`: Exposes **[Retry Now]**
- `Model Unavailable`: Exposes primary **[Switch to Nova-Lite]** and secondary **[Retry]** options.

These objects are formatted as `{ label: "String", method: "methodName", primary: boolean }`.

## Chat Layout "Dead Space"

The core chat application view `resources/views/ai/chat.blade.php` utilizes specific TailWind flex-
grow alignments avoiding `calc(100vh - 300px)` which historically trapped screens in scrolling cages
on short mobile viewports.

To modify spatial structures, manipulate:
- `<div class="page-stack h-[calc(100vh-6rem)] ...">`
- Inner container: `flex flex-col flex-1 min-h-0`

## Contract Testing

`tests/Feature/AIChatTest.php` locks all JSON permutations via explicit
`assertJsonStructure(['success', 'data' => ['key_name']])` syntax. These tests will hard fail if
middleware accidentally bypasses standardization.
