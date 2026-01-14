# Technical Specifications Index

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Status**: Complete (7 Technical Specifications)

---

## Overview

The Technical Specifications (SPEC-001 through SPEC-007) provide detailed implementation guidance for each of the seven core modules of the Umamusume Career Planner system. Each SPEC document divides and expands the corresponding PRD with comprehensive technical architecture, data models, API contracts, database schemas, and testing requirements.

---

## Specifications Catalog

### SPEC-001: Character Management System

**Covers**: PRD-001  
**Focus**: Character lifecycle, stat tracking, aptitude management, inheritance optimization

**Key Components**:
- Character entity model with stat system (Speed/Stamina/Power/Guts/Wit)
- Aptitude ratings (Distance, Surface, Running Style)
- Factor inheritance system (Stat, Aptitude, Unique Skill, Normal Skill factors)
- Growth rate application and multipliers
- Goal management and progress tracking
- Condition system (positive/negative status effects)
- Character snapshots for versioning

**API Endpoints**: 15+
- Create/Read/Update character
- Stat and condition management
- Goal CRUD operations
- Aptitude and factor retrieval
- Snapshot management

**Database Tables**: 7
- characters, character_stats, aptitudes, factors, goals, conditions, character_snapshots

**Testing**: 30+ unit, integration, and API tests

---

### SPEC-002: Training Optimization System

**Covers**: PRD-002  
**Focus**: Training predictions, support card integration, scenario-specific mechanics

**Key Components**:
- Stat gain calculation engine (with multipliers and bonuses)
- Support card bonus matrix (specialization, limit breaks, friendship training)
- Skill hint tracking and SP cost reduction
- Training option ranking algorithm
- URA Finale vs. Unity Cup scenario mechanics
- Spirit Burst filling and team synergy bonuses
- ML-based prediction accuracy improvement

**API Endpoints**: 8+
- Training predictions with ranking
- Training recommendation (with AI)
- Training session creation
- Prediction history and accuracy

**Database Tables**: 4
- training_sessions, support_cards, skill_hints, training_predictions

**Testing**: 35+ tests covering all calculation engines

---

### SPEC-003: Race Strategy System

**Covers**: PRD-003  
**Focus**: Race preparation, strategy optimization, performance prediction

**Key Components**:
- Stat requirement analyzer (with distance-specific benchmarks)
- Competition level evaluator
- Weather impact calculator
- Running style optimizer (4 styles with aptitude/stat scoring)
- Skill recommendation engine (weather, style, track, synergy)
- Race prediction engine (placement probability, outcome scenarios)
- Pre-race preparation planner

**API Endpoints**: 6+
- Race details and requirements
- Strategy recommendation
- Performance forecast
- Complete race with outcome recording

**Database Tables**: 2
- races, race_strategies

**Testing**: 25+ tests

---

### SPEC-004: Skill Management System

**Covers**: PRD-004  
**Focus**: Skill acquisition, hint tracking, evolution mechanics, SP optimization

**Key Components**:
- Skill catalog with categories (Normal, Rare, Unique)
- Hint-based SP cost reduction (20% per hint, 40% max)
- Skill evolution system (Normal → Rare, e.g., "Go with the Flow" → "Lane Legerdemain")
- Skill acquisition tracking
- Support card skill provision database
- SP optimization strategies
- AI skill build recommendations

**API Endpoints**: 5+
- Skill catalog retrieval
- Character skill list
- Skill acquisition
- Skill recommendations

**Database Tables**: 2
- skills, skill_acquisitions

**Testing**: 20+ tests

---

### SPEC-005: Support Card Management System

**Covers**: PRD-005  
**Focus**: Deck composition, card bonuses, bond tracking, meta rankings

**Key Components**:
- Support card model (rarity, limit breaks, specialization, bond level)
- Deck composition validator (6-card deck, 5 owned + 1 borrowed)
- Limit break multiplier system
- Support card database (200+ cards with meta tiers: SS, S, A, B)
- Skill provision tracking (which skills each card provides)
- Deck tier calculator
- Optimization recommendations

**API Endpoints**: 5+
- Card database retrieval
- Deck management
- Bond level/limit break updates
- Deck recommendations

**Database Tables**: 2
- support_card_database, character_support_decks

**Testing**: 20+ tests

---

### SPEC-006: AI Advisory System

**Covers**: PRD-006  
**Focus**: Intelligent recommendations using local AI + cloud fallback

**Key Components**:
- Hybrid AI architecture (Ollama local + AWS Bedrock fallback)
- Local model integration for primary recommendations
- AWS Bedrock Claude models for complex decisions
- Cost optimization (Sonnet recommended at $3/$15 per 1M tokens)
- Advisory topics: training, race prep, skill building, career strategy
- Conversation history management
- Advice feedback and outcome tracking

**AI Models**:
- **Primary**: Ollama local neural network models
- **Fallback**: AWS Bedrock Claude 3.5 Sonnet ($3/$15), Opus ($5/$25), Haiku ($1/$5)
- **Alternative**: Mistral Large ($0.008/$0.024 per 1K)

**API Endpoints**: 6+
- General AI advice
- Topic-specific advice (training, race, skill, career)
- Interactive AI conversation

**Database Tables**: 2
- ai_conversations, ai_recommendations

**Testing**: 20+ tests including model fallback

---

### SPEC-007: External Integration System

**Covers**: PRD-007  
**Focus**: API integration, OCR processing, WebSocket updates, community tools

**Key Components**:
- External API integration (primary: umapyoi.net, fallback: UmamusumeDB)
- Circuit breaker pattern for resilience
- Intelligent fallback mechanism
- OCR screenshot processing (Tesseract + OpenCV)
- WebSocket real-time updates (Laravel Reverb)
- Community tool integration and data sharing
- Rate limiting and caching strategies

**External Data Sources**:
- **Primary**: umapyoi.net API (characters, support cards, news)
- **Fallback**: UmamusumeDB.com (verification pending)
- **Community Tools**: Uel, UmamusumeDB wiki

**API Endpoints**: 6+
- Character data from external APIs
- OCR screenshot processing
- WebSocket subscriptions
- Community tips retrieval
- Career result sharing

**Database Tables**: 3
- external_api_cache, ocr_extractions, community_shares

**Testing**: 25+ tests including resilience patterns

---

## Cross-Cutting Concerns

### Performance Optimization

All specs include caching strategies:
- **Character Data**: 5-minute cache
- **Support Card Bonuses**: 1-hour cache
- **External API Data**: 24-hour cache
- **Predictions**: 5-minute cache

### Database Indexes

Comprehensive indexing strategy across all tables:
- User-based filtering (user_id indexes)
- Time-based queries (created_at, expires_at indexes)
- Entity relationships (foreign key indexes)
- Status tracking (status, completion indexes)
- Efficient aggregations

### Error Handling

Standardized error response format:
```json
{
    "error": "Error code",
    "message": "Human-readable description",
    "http_status": 422,
    "details": { "field_errors": [...] }
}
```

### Testing Coverage

- **Total Test Requirements**: 170+
- **Unit Tests**: ~80 tests
- **Integration Tests**: ~50 tests
- **API Tests**: ~30 tests
- **Performance Tests**: ~10 tests

---

## Relationship Matrix

```
SPEC-001 (Character)
  ├── Referenced by: SPEC-002, SPEC-003, SPEC-004, SPEC-005, SPEC-006
  └── Relationships: User, Skills, Conditions, Goals

SPEC-002 (Training)
  ├── Depends on: SPEC-001, SPEC-005
  ├── Referenced by: SPEC-006, SPEC-007
  └── Core flow: Character → Training → Stat Update

SPEC-003 (Race)
  ├── Depends on: SPEC-001, SPEC-002
  ├── Referenced by: SPEC-006
  └── Flow: Character → Race → Result Recording

SPEC-004 (Skills)
  ├── Depends on: SPEC-001, SPEC-002, SPEC-005
  ├── Referenced by: SPEC-006
  └── Integration: Character Skills, Support Card Skills

SPEC-005 (Support Cards)
  ├── Depends on: SPEC-002
  ├── Referenced by: SPEC-004, SPEC-006
  └── Core interaction: Deck Configuration, Training Bonuses

SPEC-006 (AI Advisory)
  ├── Depends on: All other specs (data aggregation)
  ├── Produces: Recommendations across all modules
  └── Integration: Ollama + AWS Bedrock

SPEC-007 (External)
  ├── Integrates with: All specs (data synchronization)
  ├── Provides: Real-time updates, OCR, community sharing
  └── Resilience: Circuit breaker, fallbacks, caching
```

---

## Implementation Roadmap

### Phase 1: Foundation (Weeks 1-4)
- Implement SPEC-001 (Character Management)
- Implement SPEC-005 (Support Cards - data layer only)
- Complete database migrations
- Write core models and repositories

### Phase 2: Optimization Engine (Weeks 5-8)
- Implement SPEC-002 (Training Optimization)
- Integrate SPEC-005 (card bonuses)
- Build prediction calculation engines
- ML-based accuracy improvement

### Phase 3: Race System (Weeks 9-12)
- Implement SPEC-003 (Race Strategy)
- Race prediction and strategy optimization
- Complete race workflow
- Post-race analysis

### Phase 4: Skills & AI (Weeks 13-16)
- Implement SPEC-004 (Skill Management)
- Implement SPEC-006 (AI Advisory - basic)
- Ollama integration
- Recommendation generation

### Phase 5: Integration & Polish (Weeks 17-22)
- Implement SPEC-007 (External Integration)
- AWS Bedrock fallback implementation
- OCR processing
- WebSocket real-time updates
- Community integration
- Performance optimization

### Phase 6: Testing & Release (Weeks 23-28)
- Comprehensive testing (170+ tests)
- Performance optimization
- Security audit
- Documentation finalization
- Beta testing with community
- Public release

---

## Next Steps

After SPEC completion:
1. **TECH-FLOW**: System architecture diagrams, data flow, component interactions
2. **WIREFRAMES**: UI mockups and component specifications
3. **SEQUENCES**: Detailed flow diagrams for critical operations
4. **USER FLOWS**: Complete user journey maps
5. **VERIFICATION MATRIX**: Test coverage and implementation tracking

---

**Document Index**: [000_DOCUMENT_INDEX.md](../000_DOCUMENT_INDEX.md)  
**Related**: [001_SDP](../001_SDP_Software_Development_Plan.md), [003_SRS](../003_SRS_Software_Requirement_Specifications.md)

