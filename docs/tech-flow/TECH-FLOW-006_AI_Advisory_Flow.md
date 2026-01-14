# TECH-FLOW-006: AI Advisory - Technical Flow & Task Breakdown

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (AI Advisory Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (AI Integration Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 6.x: AI Advisory System)

**Related Artifacts**:

- PRD: [PRD-006](../prds/PRD-006_AI_Advisory.md)
- SPEC: [SPEC-006](../specs/SPEC-006_AI_Advisory_Technical.md)
- Flow: [FLOW-006](../flows/FLOW-006_AI_Advisory_System.md)
- Wireframes: [WF-012](../wireframes/WF-012_AI_Advisor_Interface.md)
- Sequences: [SEQ-006](../sequences/SEQ-006_AI_Advice_Generation.md)
- User Flows: [UF-007](../user-flows/UF-007_AI_Advisor_Journey.md)
- MCP Config: [MCP_SERVER_CONFIGURATION_REFERENCE](../MCP_SERVER_CONFIGURATION_REFERENCE.md)

## System Architecture

```
┌────────────────────────────────────────────────────────────────┐
│              AI ADVISORY SYSTEM FLOW                           │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  UI Layer (Blade Templates / Vue Components)                 │
│  ├── AI Advisor Chat Interface                                │
│  ├── Advice History Panel                                     │
│  └── Context Status Display                                   │
│           ↓                                                     │
│  API Layer (REST Endpoints)                                   │
│  ├── POST /api/v1/ai/advice                                   │
│  ├── GET /api/v1/ai/advice/history                            │
│  └── GET /api/v1/ai/context                                   │
│           ↓                                                     │
│  Service Layer (Business Logic)                               │
│  ├── AIAdvisoryService (Orchestrator)                         │
│  ├── OllamaService (Local AI)                                 │
│  └── BedrockService (Cloud AI Fallback)                       │
│           ↓                                                     │
│  External Services                                            │
│  ├── Ollama API (Local: localhost:11434)                      │
│  └── AWS Bedrock API (Cloud Fallback)                         │
│           ↓                                                     │
│  Database Layer (MySQL)                                       │
│  ├── ai_advice_history table                                  │
│  └── ai_context_snapshots table                               │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagrams

### 6.1 AI Advice Request Flow (Hybrid Architecture)

```
User asks for advice
    ↓
AIAdvisoryController::getAdvice()
    ↓
AIAdvisoryService::generateAdvice()
    │
    ├─→ Gather character context
    │   ├─→ Current stats (Speed, Stamina, Power, Guts, Wit)
    │   ├─→ Active goals and progress
    │   ├─→ Support deck composition
    │   ├─→ Owned skills and SP balance
    │   └─→ Training history (last 5 sessions)
    │
    ├─→ Try OllamaService (Primary: Local AI)
    │   ├─→ POST http://localhost:11434/api/generate
    │   ├─→ Model: llama3.1:8b or mistral:7b
    │   ├─→ Include full context JSON
    │   ├─→ Temperature: 0.7
    │   └─→ If successful → Return advice
    │
    ├─→ If Ollama fails → BedrockService (Fallback: Cloud AI)
    │   ├─→ AWS Bedrock API call
    │   ├─→ Model: Claude 3 Sonnet or Mistral Large
    │   ├─→ Same context payload
    │   └─→ Return advice
    │
    ├─→ Parse AI response
    ├─→ Structure as AdviceResponse
    ├─→ Save to ai_advice_history
    └─→ Return formatted advice
            ↓
Response with AI advice + metadata
```

### 6.2 Context Snapshot Creation Flow

```
Character state changes (training/race/skill acquisition)
    ↓
Event Listener (CharacterStateChanged)
    ↓
AIContextService::createSnapshot()
    │
    ├─→ Capture current character state
    ├─→ Include recent actions (last 10)
    ├─→ Calculate context hash for deduplication
    ├─→ Save to ai_context_snapshots table
    └─→ Prune old snapshots (keep last 50)
            ↓
        Context available for next AI request
```

### 6.3 Ollama Health Check Flow

```
Scheduled Task (every 5 minutes)
    ↓
OllamaHealthCheckCommand::handle()
    ↓
OllamaService::checkHealth()
    │
    ├─→ GET http://localhost:11434/api/tags
    ├─→ If 200 OK → Mark as available
    ├─→ If timeout/error → Mark as unavailable
    └─→ Log status to ollama_health_log
            ↓
        Update AIAdvisoryService fallback routing
```

## Implementation Tasks

### Task 6.1: Ollama Integration (Week 1, ~16 hours)

- [ ] **6.1.1**: Create OllamaService
  - HTTP client for Ollama API (localhost:11434)
  - Model selection (llama3.1:8b, mistral:7b)
  - Request/response handling
  - Error handling and timeouts
  - Unit tests: 6 tests
  - **Files**: `app/Services/OllamaService.php`
  - **Effort**: 8 hours

- [ ] **6.1.2**: Create OllamaHealthCheckCommand
  - Scheduled health monitoring
  - Availability tracking
  - Automatic fallback triggering
  - Unit tests: 3 tests
  - **Files**: `app/Console/Commands/OllamaHealthCheckCommand.php`
  - **Effort**: 4 hours

- [ ] **6.1.3**: Configure Ollama models
  - Pull required models (ollama pull llama3.1:8b)
  - Test model responses
  - Document model capabilities
  - **Files**: Documentation in SPEC-006
  - **Effort**: 4 hours

### Task 6.2: Bedrock Integration (Week 1-2, ~14 hours)

- [ ] **6.2.1**: Create BedrockService
  - AWS SDK integration
  - Claude 3 Sonnet / Mistral Large support
  - Request formatting for Bedrock API
  - Cost tracking per request
  - Unit tests: 6 tests
  - **Files**: `app/Services/BedrockService.php`
  - **Effort**: 10 hours

- [ ] **6.2.2**: Configure AWS credentials
  - IAM role setup for Bedrock access
  - Environment variable configuration
  - Region selection (us-east-1)
  - **Files**: `.env`, `config/services.php`
  - **Effort**: 2 hours

- [ ] **6.2.3**: Implement cost monitoring
  - Track API calls and tokens
  - Set budget alerts
  - **Files**: `app/Services/BedrockCostMonitor.php`
  - **Effort**: 2 hours

### Task 6.3: AI Advisory Orchestration (Week 2, ~16 hours)

- [ ] **6.3.1**: Create AIAdvisoryService
  - Orchestrate Ollama → Bedrock fallback
  - Context gathering from character state
  - Prompt engineering for training/race/skill advice
  - Response parsing and formatting
  - Unit tests: 8 tests
  - **Files**: `app/Services/AIAdvisoryService.php`
  - **Effort**: 12 hours

- [ ] **6.3.2**: Create AIContextService
  - Snapshot character state for AI context
  - Prune old snapshots (retain last 50)
  - Context hash for deduplication
  - Unit tests: 4 tests
  - **Files**: `app/Services/AIContextService.php`
  - **Effort**: 4 hours

### Task 6.4: Database & Models (Week 2, ~6 hours)

- [ ] **6.4.1**: Create ai_advice_history migration
  - Fields: character_id, query, response, ai_provider, tokens_used, created_at
  - **Files**: `database/migrations/*_create_ai_advice_history_table.php`
  - **Effort**: 2 hours

- [ ] **6.4.2**: Create ai_context_snapshots migration
  - Fields: character_id, context_hash, context_json, created_at
  - **Files**: `database/migrations/*_create_ai_context_snapshots_table.php`
  - **Effort**: 2 hours

- [ ] **6.4.3**: Create Eloquent models
  - AIAdviceHistory model
  - AIContextSnapshot model
  - Unit tests: 4 tests
  - **Files**: `app/Models/AIAdviceHistory.php`, `app/Models/AIContextSnapshot.php`
  - **Effort**: 2 hours

### Task 6.5: API Layer (Week 3, ~10 hours)

- [ ] **6.5.1**: Create AIAdvisoryController
  - POST /api/v1/ai/advice (request advice)
  - GET /api/v1/ai/advice/history (advice log)
  - GET /api/v1/ai/context (current context)
  - GET /api/v1/ai/status (Ollama/Bedrock availability)
  - **Files**: `app/Http/Controllers/API/AIAdvisoryController.php`
  - **Effort**: 6 hours

- [ ] **6.5.2**: Create Form Requests
  - AIAdviceRequest (validation for query)
  - Rate limiting (max 10 requests/minute)
  - **Files**: `app/Http/Requests/AIAdviceRequest.php`
  - **Effort**: 4 hours

### Task 6.6: Testing & Integration (Week 3, ~12 hours)

- [ ] **6.6.1**: Feature tests
  - Complete advice request flow
  - Ollama → Bedrock fallback scenario
  - Context snapshot creation
  - Advice history retrieval
  - Feature tests: 8 tests
  - **Files**: `tests/Feature/AIAdvisoryTest.php`
  - **Effort**: 8 hours

- [ ] **6.6.2**: Mock external services
  - Mock Ollama responses for testing
  - Mock Bedrock responses for testing
  - Unit tests: 6 tests
  - **Effort**: 4 hours

## Summary

**Total Effort**: ~74 hours (3-4 weeks)

**Total Tests**: 20+ (15 unit tests, 8 feature tests)

**Key Deliverables**:

- Hybrid AI architecture (Local Ollama + Cloud Bedrock)
- 3 services (AIAdvisoryService, OllamaService, BedrockService)
- Health monitoring and automatic fallback
- Context-aware advice generation
- Cost tracking for cloud AI usage
- 4 REST endpoints for AI interaction
- 2 database tables for history and context

**Dependencies**:

- Requires Ollama installed locally (localhost:11434)
- Requires AWS account with Bedrock access
- Integrates with SPEC-001 (Character state for context)
- Uses SPEC-002, SPEC-003, SPEC-004 data for comprehensive advice

**Infrastructure Requirements**:

- Ollama server running locally (Docker or native)
- AWS credentials with Bedrock permissions
- Sufficient disk space for Ollama models (~8GB per model)
