# AI Recommendation Parser

## Overview

The `RecommendationParser` service parses AI responses from the Neuron AI infrastructure (Ollama + AWS Bedrock) into structured `Recommendation` value objects. It handles malformed responses, validates AI output, and provides fallback strategies for natural language parsing.

## Features

- **JSON Parsing**: Extracts structured JSON from AI responses
- **Markdown Support**: Parses JSON from markdown code blocks
- **Validation**: Validates recommendation structure against schema
- **Error Handling**: Gracefully handles malformed responses
- **Fallback Strategy**: Falls back to natural language parsing when JSON fails
- **Priority Inference**: Infers priority from keywords in natural language
- **Negation Detection**: Detects negated keywords (e.g., "not critical")

## Usage

### Basic Parsing

```php
use App\Services\AI\RecommendationParser;
use App\Enums\RecommendationType;

$parser = new RecommendationParser();

// AI response from NeuronAIService
$aiResponse = [
    'content' => json_encode([
        'type' => 'training_facility',
        'priority' => 'high',
        'action' => 'Train at Speed facility',
        'reasoning' => 'Three support cards present with high bond levels',
        'expected_outcomes' => ['Speed gain: +45-55', 'Bond increases: +7 each'],
        'risks' => ['5% failure rate due to energy level'],
        'confidence_score' => 0.92,
    ]),
    'model' => 'claude-3-5-sonnet',
    'provider' => 'bedrock',
];

// Parse to Recommendation object
$recommendation = $parser->parse($aiResponse);

// Access recommendation properties
echo $recommendation->action; // "Train at Speed facility"
echo $recommendation->priority->value; // "high"
echo $recommendation->reasoning; // "Three support cards present..."
```

### Parsing Multiple Recommendations

```php
$aiResponse = [
    'content' => json_encode([
        'recommendations' => [
            [
                'priority' => 'high',
                'action' => 'Train Speed',
                'reasoning' => 'Three support cards present',
            ],
            [
                'priority' => 'medium',
                'action' => 'Train Stamina',
                'reasoning' => 'Two support cards present',
            ],
        ],
    ]),
    'model' => 'claude-3-5-sonnet',
    'provider' => 'bedrock',
];

$recommendations = $parser->parseMultiple($aiResponse);

foreach ($recommendations as $rec) {
    echo $rec->action . "\n";
}
```

### Parsing with Fallback

```php
// Attempt parsing with automatic fallback to natural language
$recommendation = $parser->parseWithFallback($aiResponse);

if ($recommendation === null) {
    // All parsing strategies failed
    Log::error('Unable to parse AI recommendation');
}
```

## Expected JSON Schema

The parser expects AI responses to follow this schema:

```json
{
  "type": "training_facility|skill_purchase|race_strategy|rest_recovery|bond_building",
  "priority": "critical|high|medium|low",
  "action": "string (min 3 chars)",
  "reasoning": "string (min 10 chars)",
  "expected_outcomes": ["array", "of", "strings"],
  "risks": ["array", "of", "strings"],
  "confidence_score": 0.92
}
```

### Required Fields

- `priority`: Must be one of: critical, high, medium, low
- `action`: Minimum 3 characters
- `reasoning`: Minimum 10 characters

### Optional Fields

- `type`: Recommendation type (defaults to expected type parameter)
- `expected_outcomes`: Array of expected outcome strings
- `risks`: Array of risk strings
- `confidence_score`: Float between 0 and 1

## Supported Formats

### 1. Pure JSON

```json
{
  "priority": "high",
  "action": "Train Speed",
  "reasoning": "Best option available"
}
```

### 2. Markdown Code Block

```markdown
Here's my recommendation:

```json
{
  "priority": "high",
  "action": "Train Speed",
  "reasoning": "Best option available"
}
```

This should help.

```

### 3. Mixed Text with JSON

```

Based on the analysis, I recommend: {"priority": "high", "action": "Train Speed", "reasoning": "Best option"}

```

### 4. Natural Language (Fallback)

```

I strongly recommend training at the Speed facility immediately. This is critical because your character needs to improve speed stats urgently.

```

## Natural Language Parsing

When JSON parsing fails, the parser attempts to extract structured data from natural language:

### Priority Inference

- **Critical**: Keywords like "critical", "urgent", "immediately", "must", "emergency"
- **High**: Keywords like "important", "should", "recommended", "strongly"
- **Low**: Keywords like "optional", "consider", "might", "could"
- **Medium**: Default when no keywords match

### Negation Detection

The parser detects negated keywords:

- "not critical" → Does NOT infer critical priority
- "not urgent" → Does NOT infer critical priority
- "no emergency" → Does NOT infer critical priority

### Outcome Extraction

Extracts sentences containing:
- "expect", "result", "gain", "increase", "improve"

### Risk Extraction

Extracts sentences containing:
- "risk", "danger", "warning", "caution", "may fail"

## Error Handling

### Invalid Content

```php
try {
    $recommendation = $parser->parse($aiResponse);
} catch (InvalidArgumentException $e) {
    // Handle parsing error
    Log::error('Failed to parse recommendation: ' . $e->getMessage());
}
```

### Common Errors

- `AI response missing content field`: Response doesn't have 'content' key
- `AI response content is empty`: Content is empty or whitespace
- `Unable to extract valid JSON`: No valid JSON found in content
- `Invalid recommendation structure`: JSON doesn't match schema

## Integration with TrainingAdvisoryService

```php
use App\Services\Neuron\NeuronAIService;
use App\Services\AI\RecommendationParser;

class TrainingAdvisoryService
{
    public function __construct(
        protected NeuronAIService $aiService,
        protected RecommendationParser $parser
    ) {}

    public function getTrainingRecommendations(TrainingContext $context): array
    {
        // Get AI response
        $aiResponse = $this->aiService->processRequest(
            $this->buildPrompt($context),
            $context->toArray()
        );

        // Parse to recommendations
        try {
            return $this->parser->parseMultiple(
                $aiResponse,
                RecommendationType::TRAINING_FACILITY->value
            );
        } catch (InvalidArgumentException $e) {
            // Fall back to rule-based advisor
            return $this->ruleBasedAdvisor->recommendTrainingFacility($context);
        }
    }
}
```

## Testing

The parser includes comprehensive unit tests covering:

- Valid JSON parsing
- Markdown code block extraction
- Mixed text with JSON
- Missing/invalid fields
- Multiple recommendations
- Natural language fallback
- Priority inference
- Negation detection
- Edge cases (unicode, special characters, long content)

Run tests:

```bash
php artisan test --filter=RecommendationParserTest
```

## Performance

- **JSON Parsing**: ~0.05s per recommendation
- **Natural Language Parsing**: ~0.06s per recommendation
- **Validation**: ~0.01s per recommendation

## Best Practices

1. **Always use expected type parameter**: Helps parser determine correct type when missing
2. **Handle exceptions**: Wrap parsing in try-catch for production code
3. **Use parseWithFallback**: For user-facing features where reliability is critical
4. **Log parsing failures**: Track malformed responses for prompt improvement
5. **Validate confidence scores**: Lower confidence (<0.7) may indicate uncertain recommendations

## Future Enhancements

- Support for additional recommendation types
- Machine learning-based priority inference
- Multi-language support for natural language parsing
- Streaming parser for real-time recommendations
- Schema versioning for backward compatibility

## Related Documentation

- [AI Training Advisory System Design](.kiro/specs/ai-training-advisory/design.md)
- [Recommendation Value Object](../app/ValueObjects/Recommendation.php)
- [NeuronAIService](../app/Services/Neuron/NeuronAIService.php)
- [TrainingAdvisoryService](../app/Services/TrainingAdvisoryService.php)
