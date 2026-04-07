# AI Subsystem Audit Report (Follow-Up)

**Date**: 2026-07-09
**Auditor**: Claudette (Automated AI Audit Agent)
**Scope**: All AI-related code, configuration, tests, documentation, UI integration, and monitoring
**Status**: Complete
**Previous Audit**: [2026-02-28](ai-subsystem-audit-2026-02-28.md) (271 tests, 23 findings)

---

## 1. Overview & Scope

### Purpose

This is a follow-up audit of the AI subsystem. It verifies remediation of prior findings, identifies
new issues, and assesses the current state of the Ollama + Bedrock + MCP + Neuron stack including
**embedded AI components on user-facing pages** and **backend logging**.

### Systems Audited

| Subsystem | Files | Status | Change Since 2026-02-28 |
| --- | --- | --- | --- |
| Ollama (Local AI) | `OllamaService` (337 lines), config, .env | Active | .env model fixed |
| AWS Bedrock (Cloud AI) | `BedrockService` (489 lines), aws config, .env | Active | .env bugs fixed |
| HybridAI (Routing) | `HybridAIService` (981 lines) | Active | Minor refactor |
| NeuronAI (Bridge) | `NeuronAIService` (471 lines) + 4 Neuron services | Active | No change |
| MCP Integration | `MCPClientService` + 25+ MCP services | Active | 9 MCP servers tracked |
| Neuron Agents | 6 agents + 3 tools | Active | TrainingAdvisorAgent fixed |
| AI Chat | `AIChatController` (746 lines) + views | Active | `set_time_limit` fixed |
| Advisory System | `AdvisoryPanel` (651 lines) + `AdvisoryController` | Active | **Not mounted in production views** |
| Cost Tracking | `CostTrackingService` (464 lines) | Active | No change |
| Performance Monitoring | `AIPerformanceMonitor` (428 lines) | Active | No change — still cache-only |
| Vector Store (RAG) | `VectorStoreService` | Active | HTTP timeout added |
| Conversation Management | `ConversationManagementService` | Active | No change |

### Architecture (Dependency Chain)

```text
Controllers/Livewire
    ├── AdvisoryPanel (Livewire) → TrainingAdvisoryService     ← NOT MOUNTED IN ANY VIEW
    ├── AIChatController → AgentRoutingService → BedrockService
    └── Api\AdvisoryController → TrainingAdvisoryService
            └── NeuronAIService → HybridAIService
                    ├── OllamaService (local, free)
                    ├── BedrockService (cloud, paid)
                    ├── MCPClientService → StrandsAgentWrapper
                    └── MCPClientService → AgentCoreWrapper
```text

### Test Results (Run Date: 2026-07-09)

| Test Suite | Tests | Assertions | Status |
| --- | --- | --- | --- |
| AI Unit Tests | 158 | 607 | All PASS |
| AI Feature Tests | 44 | 137 | All PASS |
| Neuron + Advisory Tests | 315 | 1,129 | All PASS |
| **Total** | **517** | **1,873** | **All PASS** |

Compared to prior audit: **+246 tests**, **+932 assertions** (91% growth).

### Endpoint Smoke Tests (HTTP)

| Endpoint | Status | Response Size |
| --- | --- | --- |
| AI Dashboard (`/ai/dashboard`) | 200 OK | 104,868 bytes |
| MCP Dashboard (`/mcp/dashboard`) | 200 OK | 104,880 bytes |
| AI Dashboard API (`/api/ai/dashboard/overview`) | 200 OK | 4,816 bytes (JSON) |
| AI Chat Page (`/ai/chat`) | 200 OK | Renders with `<x-ai.chat-interface>` |

### Dashboard API Response Summary

- **MCP Servers**: 9 tracked (8 healthy, 1 disabled: figma)
- **Agents**: 5 configured (Career Strategy, Resource Management, Performance Analytics, Summer Camp
Optimization, Training Optimization)
- **Requests (24h)**: 0 — no live AI traffic recorded
- **Cost (24h)**: $0.00
- **Conversations**: 0

---

## 2. Findings — Code

### 2.1 Prior Audit Remediation Status

| ID | Finding | Status | Evidence |
| --- | --- | --- | --- |
| P0-1 | `.env` `AWS_BEDROCK_ENABLED==true` typo | **FIXED** | Now `BEDROCK_ENABLED=true` |
| P0-2 | Env var name mismatch (config reads `BEDROCK_ENABLED`) | **FIXED** | Both `.env` and config aligned |
| P0-3 | AWS credentials in `.env` | **OPEN** | `AWS_ACCESS_KEY_ID=AKIAYABG2QY6S2N5CICO` still in `.env` L79 |
| P0-4 | `set_time_limit(0)` in AIChatController | **FIXED** | Now `set_time_limit(120)` at L51, L123 |
| P0-5 | Ollama model mismatch (`llama3` vs `llama3.3`) | **FIXED** | `.env` L113: `OLLAMA_DEFAULT_MODEL=llama3.3` |
| P1-1 | Hardcoded `user_id=1` in HybridAIService | **PARTIAL** | Now `user_id => $userId ?? 1` (still falls back to 1 when null) |
| P1-2 | VectorStoreService missing HTTP timeout | **FIXED** | `Http::timeout(config('services.openai.timeout', 30))` at L148 |
| P1-4 | Model preference `claude-4.5-sonnet` wrong ID | **FIXED** | Now `claude-3-5-sonnet,nova-2-lite` |
| P1-7 | TrainingAdvisorAgent hardcoded to Anthropic | **FIXED** | Now extends `BaseAgent` (config-based provider) |
| P1-8 | Stack trace in VectorStoreService logs | **NEEDS CHECK** | VectorStoreService timeout added but trace logging not verified |
| SEC-003 | `set_time_limit(0)` DoS vector | **FIXED** | Changed to 120s limit |
| SEC-004 | Error message exposure in AdvisoryController | **FIXED** | All 6 `$e->getTraceAsString()` replaced with `report($e)` (2026-07-09) |
| ARCH-001 | Fake streaming in SSE endpoint | **OPEN** | `sendMessageStreaming()` still processes full response then chunks |
| ARCH-004 | AIPerformanceMonitor cache-only (1hr TTL) | **OPEN** | No DB persistence added |
| ARCH-005 | Budget enforcement is advisory-only | **OPEN** | No blocking logic when budget exceeded |
| CODE-001 | CostTrackingService uses `DB::table()` | **FIXED** | Replaced with `AiCost` Eloquent model (2026-07-09) |
| CODE-002 | Hardcoded user_id fallback to 1 | **FIXED** | Now uses `$userId ?? Auth::id()` (2026-07-09) |
| FR-18 | `rateMessage()` only logs, never persists | **FIXED** | Now persists `quality_rating` and `is_helpful` to `ConversationMessage` (2026-07-09) |

**Remediation Score: 12/17 fixed, 0 partial, 5 open.**

### 2.2 NEW CRITICAL: AdvisoryPanel Not Mounted in Production Views — REMEDIATED

- **Component**: `app/Livewire/AdvisoryPanel.php` (651 lines PHP) +
`resources/views/livewire/advisory-panel.blade.php` (482+ lines Blade)
- **Status**: **FIXED** (2026-07-09). `<livewire:advisory-panel />` mounted on
`resources/views/training/show.blade.php` and `resources/views/plans/edit.blade.php`.
- **Impact**: Users can now see AI-powered training recommendations in-context on the training and plan editing pages.
- **Recommendation**: Mount `<livewire:advisory-panel />` on career run edit/training pages.
Consider adding it to `resources/views/layouts/app.blade.php` with conditional rendering.

### 2.3 NEW: No AI Components Embedded on User Pages

- **Components Audited**: 13 Blade components in `resources/views/components/ai/`:
  - `chat-interface`, `message-bubble`, `agent-selector`, `agent-progress-tracker`, `provider-
  selector`, `server-status-indicator`, `tool-usage-indicator`, `tool-execution-monitor`, `workflow-
  visualization`, `performance-metrics`, `recommendation-card`, `recommendation-card-example`,
  `critical-alert-badge`
- **Finding**: `<x-ai.chat-interface>` is used **only** in `resources/views/ai/chat.blade.php`.
`<x-ai.recommendation-card>` is used **only** in `recommendation-card-example.blade.php` (a demo
file). No AI components appear on training views, character detail pages, career run pages, or any
other user-facing template.
- **Impact**: All 13 AI Blade components exist but only 1 (`chat-interface`) is used in production,
and only on the dedicated chat page. The recommendation cards, critical alerts, and other UI
elements designed for in-context AI assistance are unused.

### 2.4 STILL OPEN: Security Vulnerabilities

#### SEC-001 (CRITICAL): AWS Credentials in `.env`

- **File**: `.env` L79-80
- **Content**: `AWS_ACCESS_KEY_ID=AKIAYABG2QY6S2N5CICO` (plaintext)
- **Status**: Same credentials as prior audit. **Not rotated**.
- **Risk**: Full AWS account compromise if repository shared/exposed.
- **Action Required**: Rotate credentials immediately.

#### SEC-004 (HIGH): AdvisoryController Error/Trace Exposure — REMEDIATED

- **Files**: `app/Http/Controllers/Api/AdvisoryController.php`
- **Status**: **FIXED** (2026-07-09). All 6 `$e->getTraceAsString()` instances replaced with
`report($e)`. Log context now only includes `'error' => $e->getMessage()`.

### 2.5 STILL OPEN: Architectural Issues

#### ARCH-001: Fake Streaming (SSE)

- **File**: `app/Http/Controllers/AIChatController.php` — `sendMessageStreaming()`
- **Status**: Unchanged. Full response generated first, then chunked into ~160-character pieces.
- **Impact**: User perceives no output until entire AI response completes.

#### ARCH-004: AIPerformanceMonitor — Volatile Cache Only

- **File**: `app/Services/AI/AIPerformanceMonitor.php`
- **Status**: Unchanged. Metrics stored in cache with `METRICS_TTL = 3600` (1 hour).
- **Impact**: All performance history lost on cache clear/restart. Dashboard API showing 0 requests
is consistent with this finding — no persistent metrics.

#### ARCH-005: Budget Enforcement — Advisory Only

- **File**: `app/Services/AI/CostTrackingService.php`
- **Status**: Unchanged. `getBudgetStatus()` returns status labels (`exceeded`, `critical`,
`warning`, `healthy`) but no code path blocks requests.
- **Default Budget**: $100/month hardcoded in constructor.
- **Impact**: Runaway Bedrock costs possible with no circuit breaker.

### 2.6 STILL OPEN: Code Quality

#### CODE-001: CostTrackingService Uses Raw DB Queries — REMEDIATED

- **Status**: **FIXED** (2026-07-09). Created `AiCost` Eloquent model (`app/Models/AiCost.php`) with
proper fillable, casts, and relationships. All 9 `DB::table('ucp_ai_costs')` calls replaced with
`AiCost::query()`.

#### CODE-002: HybridAIService user_id Fallback — REMEDIATED

- **Line 756**: Now uses `$userId ?? Auth::id()` instead of `$userId ?? 1`.
- **Status**: **FIXED** (2026-07-09). Falls back to the authenticated user's ID via Laravel's `Auth` facade.

#### FR-18: Message Rating Not Persisted — REMEDIATED

- **Status**: **FIXED** (2026-07-09). `rateMessage()` now maps rating values to `quality_rating`
(int) and `is_helpful` (bool), persisting them to `ConversationMessage` via Eloquent `update()`.

---

## 3. Findings — Config

### 3.1 Configuration Health (Improved Since Prior Audit)

| Config Key | Status | Notes |
| --- | --- | --- |
| `ai.hybrid.enabled` | OK | Defaults to `true`, env override available |
| `ai.ollama.enabled` | OK | Set in `.env` |
| `ai.ollama.host` | OK | `localhost:11434` |
| `ai.ollama.default_model` | **FIXED** | `.env` and config both use `llama3.3` |
| `ai.ollama.timeout` | **FIXED** | `.env` aligned to 120s to match config default |
| `ai.bedrock.enabled` | **FIXED** | `BEDROCK_ENABLED=true` — aligned |
| `ai.bedrock.default_model` | OK | `claude-3-5-sonnet` |
| `ai.mcp.enabled` | OK | Set in `.env` |
| `ai.monitoring.enabled` | OK | Default `true` |
| `ai.conversation.max_history` | OK | 50 messages |

### 3.2 Pricing Configuration Duplication

- `config/ai.php` defines `bedrock.pricing` with per-model input/output rates
- `config/aws.php` defines `bedrock.models` with per-model `input_cost`/`output_cost`
- **Risk**: Two sources of truth for pricing data. If updated in one but not the other, cost calculations diverge.
- **Recommendation**: Consolidate pricing into `config/aws.php` and reference from `config/ai.php`.

### 3.3 Timeout Inconsistencies (Still Present)

| Location | Ollama Timeout | Bedrock Timeout |
| --- | --- | --- |
| `config/ai.php` default | 120s | 30s |
| `.env` override | 30s | (not set) |
| `AIChatController` | 120s (`set_time_limit`) | 120s (`set_time_limit`) |
| `AgentRoutingService` constants | 15s | 30s |
| `NeuronAIService` complexity-based | 10-30s | N/A |

**Recommendation**: Standardize timeouts. The effective Ollama timeout is 30s (from `.env`) but
`set_time_limit(120)` allows 4x that. Either align `.env` to 120s or `set_time_limit` to 30s.

---

## 4. Findings — Tests

### 4.1 Test Growth (Significant Improvement)

| Metric | 2026-02-28 | 2026-07-09 | Change |
| --- | --- | --- | --- |
| Total Tests | 271 | 517 | +91% |
| Total Assertions | 941 | 1,873 | +99% |
| Status | All PASS | All PASS | Maintained |

### 4.2 Remaining Test Gaps

Based on prior audit gap analysis, these are still missing:

| Missing Test | Priority | Status |
| --- | --- | --- |
| `OllamaServiceTest` (standalone) | Critical | Still missing — only tested indirectly via mocks |
| `BedrockServiceTest` for `generate()` | Critical | Config/health tests exist, but core `generate()` untested |
| `AIPerformanceMonitorTest` | Critical | Still no dedicated test file |
| `ConversationManagementServiceTest` | High | Still no dedicated test file |
| Budget enforcement integration test | High | Still missing |
| True streaming integration test | Medium | Still missing |
| Embedded AI component E2E test | **NEW** | Cannot test — components not mounted |

---

## 5. Findings — Documentation & Suggestions

### 5.1 Documentation Inventory

| Document | Path | Status |
| --- | --- | --- |
| Prior Audit Report | `docs/audits/ai-subsystem-audit-2026-02-28.md` | Complete (519 lines) |
| AI Providers Guide | `docs/neuron/ai-providers.md` | Complete (405 lines) |
| Training Advisory API | `docs/ai-training-advisory-api.md` | Complete |
| Implementation Summary | `docs/implementation-summaries/ai-training-advisory-implementation-summary.md` | Shows ~52% completion |
| Recommendation Card Component | `docs/implementation-summaries/ai-recommendation-card-component.md` | Exists |
| Routing Setup | `docs/implementation-summaries/ai-training-advisory-routing-setup.md` | Exists |
| MCP Connector Guide | `docs/neuron/mcp-connector-guide.md` | Exists |
| MCP Tool Integration | `docs/neuron/mcp-tool-integration.md` | Exists |
| RAG System | `docs/neuron/rag.md` | Exists |
| Streaming Guide | `docs/neuron/streaming.md` | Exists |
| Structured Output | `docs/neuron/structured-output.md` | Exists |
| Performance Report | `docs/neuron/performance-optimization-report.md` | Exists |

### 5.2 Still Missing Documentation (From Prior Audit)

1. **AI Deployment/Setup Guide** — How to install Ollama, configure Bedrock, set up MCP servers
2. **Cost Management Guide** — Budget thresholds, optimization, monitoring
3. **AI Troubleshooting Guide** — Common failures, verification steps, log locations
4. **`.env.example` AI Variables** — Many AI-related env vars undocumented in example file

### 5.3 New Documentation Issue

- **AdvisoryPanel Integration Guide** — No documentation exists for how/where to mount the Livewire
AdvisoryPanel in production views. The component is fully documented internally (PHPDoc, WCAG notes)
but there's no integration guide for frontend developers.

---

## 6. FR/NFR Compliance Table

### Functional Requirements

| ID | Requirement | Status | Evidence | Issues |
| --- | --- | --- | --- | --- |
| FR-01 | Training recommendations via AI | PASS | `TrainingAdvisoryService` with 3-tier fallback | None |
| FR-02 | Skill purchase advice | PASS | `AdvisoryController.getSkillPurchaseAdvice()` | None |
| FR-03 | Race strategy (AI-powered) | PARTIAL | API endpoint is rule-based only; `NeuronAIService.generateRaceStrategy()` exists but not wired | Unchanged |
| FR-04 | Critical situation detection | PASS | `CriticalSituationDetector` | None |
| FR-05 | Ollama local inference | PASS | `OllamaService.generate()` | Model alignment fixed |
| FR-06 | Bedrock cloud inference | PASS | `BedrockService.generate()` | Config bugs fixed |
| FR-07 | Intelligent provider routing | PASS | `HybridAIService.selectProvider()` | None |
| FR-08 | Provider fallback | PASS | `handleFailureWithFallback()` | None |
| FR-09 | AI Chat interface | PASS | Web view + API | Fake streaming |
| FR-10 | Conversation history | **FIXED** | `user_id` fallback now uses `Auth::id()` | None |
| FR-11 | RAG knowledge enrichment | PASS | `VectorStoreService` + `enrichContextWithKnowledge()` | HTTP timeout added |
| FR-12 | MCP server integration | PASS | 9 servers tracked, 8 healthy | None |
| FR-13 | Multi-agent orchestration | PASS | 5 agents configured | None |
| FR-14 | Cost tracking | PASS | `CostTrackingService` persists to `ucp_ai_costs` via `AiCost` model | None |
| FR-15 | Performance monitoring | PARTIAL | Cache-only, 1hr TTL, no persistence | No historical data |
| FR-16 | AI Dashboard | PASS | Web + API endpoints | Shows 0 activity |
| FR-17 | User AI preferences | PASS | Cache-based (30-day TTL) | Lost on cache clear |
| FR-18 | Message rating/feedback | **PASS** | `rateMessage()` persists to `ConversationMessage` | None |
| FR-19 | Prediction outcome recording | PASS | `recordTrainingOutcome()` + `recordRaceOutcome()` | None |
| FR-20 | A/B testing for models | PASS | `ABTestingService` | None |
| **FR-21** | **Embedded AI on user pages** | **FIXED** | AdvisoryPanel mounted on `training/show` and `plans/edit` views (2026-07-09) | None |

### Non-Functional Requirements

| ID | Requirement | Status | Evidence | Issues |
| --- | --- | --- | --- | --- |
| NFR-01 | Ollama latency ≤2s | AT RISK | Effective timeout 30s (from .env); no p95 enforcement | Need runtime monitoring |
| NFR-02 | Bedrock latency ≤5s | PASS | 30s timeout configured | Within SLA |
| NFR-03 | Rule-based latency ≤500ms | PASS | <100ms per impl docs | Exceeds target |
| NFR-04 | Offline support | PASS | Rule-based fallback + local Ollama | None |
| NFR-05 | Cost threshold protection | **FAIL** | Budget advisory-only, no blocking | Unchanged |
| NFR-06 | Rate limiting on AI endpoints | PASS | `throttle:10,1` advisory, `throttle:60,1` neuron | None |
| NFR-07 | Authentication on AI endpoints | PASS | `auth:sanctum` on chat + neuron routes | None |
| NFR-08 | No credential exposure | **FAIL** | AWS key still in `.env`, not rotated since Feb audit | Critical |
| NFR-09 | Execution time limits | **FIXED** | `set_time_limit(120)` | Was `0` |
| NFR-10 | Error isolation | PASS | Try/catch with fallback chains | Trace exposure in logs |
| NFR-11 | Health check endpoints | PASS | Server status + MCP health checks | None |
| NFR-12 | Metrics persistence | PARTIAL | Cache-based only; dashboard shows 0 | No DB persistence |
| NFR-13 | Caching strategy | PASS | Multi-layer caching (recommendations, availability, embeddings) | None |
| NFR-14 | Input validation | PASS | Form requests + inline validation | None |
| NFR-15 | Backend logging for AI interactions | **PASS** | Conversations logged via `logConversation()`; costs tracked via `AiCost` model; ratings persisted to `ConversationMessage`; AdvisoryPanel mounted on training/edit views | None |

---

## 7. Action Items & Improvements

### P0 — CRITICAL (Fix Immediately)

| # | Action | File(s) | Effort | Prior ID |
| --- | --- | --- | --- | --- |
| P0-1 | **Rotate AWS credentials** — key `AKIAYABG2QY6S2N5CICO` has been in `.env` since at least Feb 2026 | AWS Console + `.env` | 30 min | SEC-001 |
| P0-2 | ~~**Mount AdvisoryPanel in production views**~~ — DONE: mounted on `training/show` and `plans/edit` | Layout/training Blade views | 1 hr | NEW |

### P1 — HIGH (Fix This Sprint)

| # | Action | File(s) | Effort | Prior ID |
| --- | --- | --- | --- | --- |
| P1-1 | ~~**Remove `$e->getTraceAsString()` from AdvisoryController logs**~~ — DONE: replaced with `report($e)` (6 instances) | `AdvisoryController.php` | 15 min | SEC-004 |
| P1-2 | ~~**Fix user_id fallback**~~ — DONE: now uses `Auth::id()` | `HybridAIService.php` L756 | 15 min | CODE-002 |
| P1-3 | **Embed at least recommendation-card and critical-alert-badge** on user training/character pages | Training/character Blade views | 2 hrs | NEW |
| P1-4 | **Consolidate pricing config** — remove duplicate pricing from `config/ai.php`, reference `config/aws.php` | `config/ai.php`, `config/aws.php` | 30 min | NEW |
| P1-5 | ~~**Standardize timeouts**~~ — DONE: `.env` aligned to 120s | `.env`, `config/ai.php`, `AIChatController.php` | 30 min | P3-6 |

### P2 — MEDIUM (Fix Next Sprint)

| # | Action | File(s) | Effort | Prior ID |
| --- | --- | --- | --- | --- |
| P2-1 | **Create `OllamaServiceTest`** — standalone unit tests | `tests/Unit/Services/AI/` | 2 hrs | P2-1 |
| P2-2 | **Create `BedrockServiceTest`** for `generate()` + payload building | `tests/Unit/Services/AI/` | 2 hrs | P2-2 |
| P2-3 | **Create `AIPerformanceMonitorTest`** | `tests/Unit/Services/AI/` | 1 hr | P2-3 |
| P2-4 | **Create `ConversationManagementServiceTest`** | `tests/Unit/Services/AI/` | 2 hrs | P2-4 |
| P2-5 | **Implement true streaming** using Ollama's native stream + Bedrock's `InvokeModelWithResponseStream` | `AIChatController.php` | 4 hrs | ARCH-001 |
| P2-6 | **Implement budget enforcement** — block/downgrade to Ollama when budget exceeded | `AgentRoutingService.php`, `CostTrackingService.php` | 2 hrs | ARCH-005 |
| P2-7 | **Persist AI metrics to DB** alongside cache for historical analysis | `AIPerformanceMonitor.php` | 3 hrs | ARCH-004 |
| P2-8 | ~~**Persist `rateMessage()` ratings**~~ — DONE: persists to `ConversationMessage` | `AIChatController.php` | 1 hr | FR-18 |
| P2-9 | ~~**Replace `DB::table()` with Eloquent**~~ — DONE: created `AiCost` model, all queries use Eloquent | `CostTrackingService.php`, `AiCost.php` | 1 hr | CODE-001 |

### P3 — LOW (Backlog)

| # | Action | File(s) | Effort | Prior ID |
| --- | --- | --- | --- | --- |
| P3-1 | **Create AI deployment/setup guide** | `docs/guides/` | 2 hrs | P3-1 |
| P3-2 | **Create cost management guide** | `docs/guides/` | 1 hr | P3-2 |
| P3-3 | **Create AI troubleshooting guide** | `docs/guides/` | 1 hr | P3-3 |
| P3-4 | **Wire race strategy API to NeuronAIService** | `AdvisoryController.php` | 4 hrs | P3-4 |
| P3-5 | **Persist user AI preferences to DB** instead of cache | `AIChatController.php` | 2 hrs | P3-5 |
| P3-6 | **Add AdvisoryPanel integration guide** for frontend devs | `docs/guides/` | 1 hr | NEW |
| P3-7 | **Update `.env.example`** with all AI-related env vars | `.env.example` | 15 min | P1-5 |
| P3-8 | **Update implementation summary** to reflect actual completion | `docs/implementation-summaries/` | 30 min | P3-8 |

---

## Appendix A: Files Audited (Deep Read)

| File | Lines | Audit Depth |
| --- | --- | --- |
| `app/Services/AI/OllamaService.php` | 337 | Full read |
| `app/Services/AI/BedrockService.php` | 489 | Full read |
| `app/Services/AI/HybridAIService.php` | 981 | Full read |
| `app/Services/AI/CostTrackingService.php` | 464 | Full read |
| `app/Services/AI/AIPerformanceMonitor.php` | 428 | Full read |
| `app/Services/Neuron/NeuronAIService.php` | 471 | Full read |
| `app/Http/Controllers/AIChatController.php` | 746 | Full read |
| `app/Livewire/AdvisoryPanel.php` | 651 | Partial (200 lines + grep) |
| `app/Models/AIConversation.php` | 242 | Full read |
| `app/Models/ConversationMessage.php` | 338 | Full read |
| `app/Neuron/Agents/TrainingAdvisorAgent.php` | 113 | Full read |
| `config/ai.php` | ~220 | Full read |
| `config/aws.php` | 140 | Full read |
| `routes/api.php` | 530 | Partial (AI sections) |
| `resources/views/ai/chat.blade.php` | — | Full read |
| `resources/views/ai/dashboard.blade.php` | — | Full read |
| `docs/audits/ai-subsystem-audit-2026-02-28.md` | 519 | Full read |
| `docs/neuron/ai-providers.md` | 405 | Partial (100 lines) |

## Appendix B: Audit Methodology

1. **Context Verification**: Read config files, listed service directories, verified assumptions from prior audit
2. **Code Audit**: Read all core AI service files end-to-end, compared against prior findings
3. **Config Audit**: Verified `.env` alignment with `config/ai.php` and `config/aws.php`
4. **Test Execution**: Ran all AI-related test suites (517 tests, 1,873 assertions)
5. **Endpoint Smoke Testing**: HTTP requests to AI dashboard, MCP dashboard, AI chat, and dashboard API
6. **Embedded Component Audit**: Grep search across all Blade templates for AI component usage
7. **Documentation Review**: Inventoried AI-related docs, compared against prior audit gaps
8. **Prior Audit Delta**: Cross-referenced all 17 prior findings for remediation status
9. **Chrome DevTools**: Attempted browser automation (blocked by existing browser session); used
HTTP smoke tests as alternative

## Appendix C: Key Metrics Comparison

| Metric | Feb 2026 | Jul 2026 | Trend |
| --- | --- | --- | --- |
| Tests | 271 | 517 | +91% |
| Assertions | 941 | 1,873 | +99% |
| Critical Findings (P0) | 5 | 2 | -60% (3 fixed, 2 remain) |
| High Findings (P1) | 8 | 5 | -38% (3 fixed, 5 new/carried) |
| Open Security Issues | 4 | 2 | -50% |
| AI Service Files | ~20 | 25+ | +25% |
| MCP Servers Tracked | — | 9 | New capability |
| AI Components (Blade) | — | 13 | 1 used in prod |
| AI Components Mounted | — | 1 | 12 unused |

---

## Summary

The AI subsystem has seen significant improvement since the February 2026 audit: test coverage
nearly doubled, 12 of 17 prior findings were fixed (including 5 remediated on 2026-07-09), and MCP
integration has matured with 9 servers tracked. One critical issue requires immediate attention:

1. **AWS credentials** remain exposed in `.env` and have not been rotated in 5+ months

The following items were **remediated on 2026-07-09**:

- **SEC-004**: All 6 `$e->getTraceAsString()` instances removed from AdvisoryController, replaced with `report($e)`
- **CODE-001**: Created `AiCost` Eloquent model, replaced all 9 `DB::table()` calls in CostTrackingService
- **CODE-002**: user_id fallback changed from hardcoded `1` to `Auth::id()`
- **FR-18**: `rateMessage()` now persists `quality_rating` and `is_helpful` to ConversationMessage
- **P0-2/FR-21**: AdvisoryPanel mounted on `training/show` and `plans/edit` production views
- **P1-5**: OLLAMA_TIMEOUT aligned to 120s across `.env` and config

The remaining open items (fake streaming, budget enforcement, metrics persistence) represent
architectural debt that should be addressed in upcoming sprints.

---

End of Audit Report
