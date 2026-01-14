# SPEC-006: AI Advisory System - Technical Specification

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

## Overview

The AI Advisory System integrates hybrid local/cloud AI (Ollama + AWS Bedrock) to provide strategic recommendations for training, race preparation, skill building, and career optimization.

## Core Components

### 6.1 AI Service Architecture

```
AI Advisory Module (SPEC-006)
├── Local AI (Ollama)
│   ├── Neural Network Models
│   ├── Recommendation Engine
│   └── Pattern Analysis
├── Cloud AI (AWS Bedrock)
│   ├── Claude Models (Opus, Sonnet, Haiku)
│   ├── Mistral Large
│   └── Llama 70B
└── Fallback & Orchestration
    ├── Model Selection Logic
    └── Error Recovery
```

### 6.2 Advisory Service

```php
class AIAdvisoryService
{
    public function __construct(
        private OllamaService $ollama,
        private BedrockService $bedrock,
        private AdvisoryCache $cache
    ) {}
    
    public function generateTrainingAdvice(Character $char): AdvisoryResponse
    {
        $cacheKey = "advice:training:{$char->id}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Primary: Use Ollama local model
        try {
            $advice = $this->ollama->generateRecommendation(
                model: 'neural-trainer-model',
                context: $this->buildContext($char),
                temperature: 0.7
            );
        } catch (Exception $e) {
            // Fallback: Use AWS Bedrock Claude
            $advice = $this->bedrock->generateRecommendation(
                model: 'claude-3-sonnet-20240229',
                context: $this->buildContext($char)
            );
        }
        
        $this->cache->put($cacheKey, $advice, 300);  // 5 min TTL
        return $advice;
    }
    
    private function buildContext(Character $char): string
    {
        return <<<CONTEXT
Character: {$char->name}
Stats: Speed {$char->stats['speed']}, Stamina {$char->stats['stamina']}, Power {$char->stats['power']}, Guts {$char->stats['guts']}, Wit {$char->stats['wit']}
Current Mood: {$char->current_mood}
Energy: {$char->current_energy}%
Days Until Race: {$char->days_until_race}
Goals: {json_encode($char->goals)}
Recent Training History: {json_encode($char->recentSessions())}
CONTEXT;
    }
}
```

### 6.3 Advisory Topics

```php
class AdvisoryTopics
{
    // Training Optimization
    public function generateTrainingAdvice(): string { }
    
    // Race Preparation
    public function generateRaceAdvice(): string { }
    
    // Skill Building
    public function generateSkillBuildAdvice(): string { }
    
    // Career Planning
    public function generateCareerAdvice(): string { }
    
    // Support Deck Optimization
    public function generateDeckAdvice(): string { }
    
    // Problem Solving
    public function generateTroubleshootingAdvice(string $problem): string { }
}
```

## API Endpoints

### GET /api/v1/characters/{id}/ai-advice
Get general AI advisory based on character state

### GET /api/v1/characters/{id}/ai-advice/training
Training-specific AI advice

### GET /api/v1/characters/{id}/ai-advice/race-prep
Race preparation advisory

### GET /api/v1/characters/{id}/ai-advice/skill-building
Skill build optimization advice

### GET /api/v1/characters/{id}/ai-advice/career
Long-term career strategy advice

### POST /api/v1/characters/{id}/ai-conversation
Interactive AI conversation for guidance

## Database Schema

```sql
CREATE TABLE ai_conversations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    topic VARCHAR(100),  // training, race, skill, career
    messages JSON,        // Conversation history
    model_used VARCHAR(50),
    execution_time_ms INT,
    created_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_character_id (character_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

CREATE TABLE ai_recommendations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    recommendation_type VARCHAR(50),
    advice_text LONGTEXT,
    confidence_score DECIMAL(5, 2),
    model_used VARCHAR(50),
    generated_at TIMESTAMP,
    acted_upon BOOLEAN DEFAULT FALSE,
    outcome_recorded BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (character_id) REFERENCES characters(id),
    INDEX idx_character_id (character_id),
    INDEX idx_acted_upon (acted_upon)
) ENGINE=InnoDB;
```

## Pricing & Cost Optimization

### AWS Bedrock Model Costs

| Model | Input Cost | Output Cost | Use Case |
| --- | --- | --- | --- |
| Claude 3.5 Sonnet | $3/1M | $15/1M | **Recommended** - Balanced |
| Claude Opus 4.5 | $5/1M | $25/1M | Complex analysis |
| Claude Haiku 4.5 | $1/1M | $5/1M | Fast responses |
| Mistral Large 2 | $0.008/1K | $0.024/1K | Cost-effective |

### Cost Optimization Strategy

- Use Ollama locally for all non-complex tasks (training recommendations, basic analysis)
- Use Bedrock only for complex decisions (career planning, multi-factor race analysis)
- Implement aggressive caching (5-minute TTL for predictions)
- Batch process recommendations when possible

## Testing

- [ ] Ollama local model integration
- [ ] AWS Bedrock fallback mechanism
- [ ] Model selection logic
- [ ] Advisory accuracy and usefulness
- [ ] Conversation context management
- [ ] Performance and latency
- [ ] Cost tracking and optimization
- [ ] Recommendation follow-up and feedback

---

**Related**: [PRD-006], [SDS], [AWS Bedrock Documentation]

