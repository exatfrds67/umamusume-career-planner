# SEQUENCE DIAGRAMS: Critical Interaction Flows

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Comprehensive

## Overview

Sequence diagrams document the detailed interactions between system components during critical operations. These diagrams show message flows, timing, and dependencies for key workflows.

---

## SD-001: Character Creation Flow

```
User                    UI                  Controller              Service              Database
 │                      │                        │                      │                   │
 │ [Fill Form]          │                        │                      │                   │
 │─────────────────────>│                        │                      │                   │
 │                      │  POST /api/characters  │                      │                   │
 │                      │───────────────────────>│                       │                   │
 │                      │                        │  store(data)         │                   │
 │                      │                        │──────────────────────>│                   │
 │                      │                        │                      │  INSERT characters│
 │                      │                        │                      │──────────────────>│
 │                      │                        │                      │  character_id=1   │
 │                      │                        │                      │<──────────────────│
 │                      │                        │  createCharacter()   │                   │
 │                      │                        │──────────────────────>│                   │
 │                      │                        │                      │  calculateInherit()
 │                      │                        │<──────────────────────│                   │
 │                      │                        │  [returns factors]   │                   │
 │                      │                        │  INSERT factors      │                   │
 │                      │                        │──────────────────────────────────────────>│
 │                      │                        │  INSERT aptitudes    │                   │
 │                      │                        │──────────────────────────────────────────>│
 │                      │                        │  INSERT goals        │                   │
 │                      │                        │──────────────────────────────────────────>│
 │                      │                        │  INSERT snapshot     │                   │
 │                      │                        │──────────────────────────────────────────>│
 │                      │                        │  Trigger Event       │                   │
 │                      │                        │  CharacterCreated    │                   │
 │                      │                        │                      │                   │
 │                      │  Character object      │                      │                   │
 │                      │<───────────────────────│                      │                   │
 │ [Display Success]    │                        │                      │                   │
 │<─────────────────────│                        │                      │                   │
 │ [Redirect Dashboard] │                        │                      │                   │
 │─────────────────────>│                        │                      │                   │
```

**Duration**: ~500ms (including database operations)  
**Critical Path**: Form validation → Database inserts → Event dispatch → Response

---

## SD-002: Training Session & Character Update

```
User              TrainingUI          TrainingController        TrainingService         CharacterService
 │                    │                       │                       │                      │
 │ [Select Training]  │                       │                       │                      │
 │───────────────────>│                       │                       │                      │
 │                    │  POST /training-sess  │                       │                      │
 │                    │──────────────────────>│                       │                      │
 │                    │                       │ completeSession()     │                      │
 │                    │                       │──────────────────────>│                      │
 │                    │                       │                       │ updateCharacter()   │
 │                    │                       │                       │────────────────────>│
 │                    │                       │                       │  UPDATE stats      │
 │                    │                       │                       │  UPDATE mood       │
 │                    │                       │                       │  UPDATE energy     │
 │                    │                       │                       │  CREATE snapshot   │
 │                    │                       │                       │<────────────────────│
 │                    │                       │ [updated character]   │                      │
 │                    │                       │<──────────────────────│                      │
 │                    │                       │ Trigger Event         │                      │
 │                    │                       │ StatsUpdated          │                      │
 │                    │                       │ [Listeners: Logger,   │                      │
 │                    │                       │  WebSocket, Cache]    │                      │
 │                    │ Training session data │                       │                      │
 │                    │<──────────────────────│                       │                      │
 │ [Refresh Display]  │                       │                       │                      │
 │<───────────────────│                       │                       │                      │
```

**Duration**: ~200ms (prediction + state update)  
**Parallel Operations**: Database writes + Event dispatch + Cache invalidation

---

## SD-003: Race Completion & Result Recording

```
User            RaceUI          RaceController        RaceService         CharacterService      Database
 │               │                    │                     │                   │                   │
 │ [Race Ends]   │                    │                     │                   │                   │
 │              │                    │                     │                   │                   │
 │  [Results]    │                    │                     │                   │                   │
 │              │ POST /races/{id}/complete                 │                   │                   │
 │              │─────────────────────────────────────────>│                   │                   │
 │              │                    │                     │ recordResult()    │                   │
 │              │                    │                     │──────────────────>│                   │
 │              │                    │                     │                   │ UPDATE character  │
 │              │                    │                     │                   │  UPDATE fans      │
 │              │                    │                     │                   │  UPDATE grade     │
 │              │                    │                     │                   │──────────────────>│
 │              │                    │                     │ INSERT race result│                   │
 │              │                    │                     │──────────────────────────────────────>│
 │              │                    │                     │ Trigger RaceCompleted event            │
 │              │                    │                     │ [Listeners: Achievement, Broadcast]   │
 │              │                    │ Race analysis       │                   │                   │
 │              │                    │<──────────────────────────────────────>│                   │
 │              │ Result + Analysis  │                     │                   │                   │
 │              │<───────────────────│                     │                   │                   │
 │ [Show Results]│                    │                     │                   │                   │
 │<─────────────│                    │                     │                   │                   │
```

**Duration**: ~300ms  
**Sequential**: Result validation → Character update → Database insert → Event dispatch

---

## SD-004: Skill Acquisition with Hint Tracking

```
User          SkillUI          SkillController       SkillService       SkillHintService      Database
 │              │                    │                    │                    │                   │
 │ [Acquire Skill]│                  │                    │                    │                   │
 │─────────────>│                  │                    │                    │                   │
 │              │ POST /skills/acquire                     │                    │                   │
 │              │──────────────────────────────────────>│                    │                   │
 │              │                    │ acquireSkill()    │                    │                   │
 │              │                    │────────────────>│                    │                   │
 │              │                    │                    │ getHints()        │                   │
 │              │                    │                    │───────────────────>│                   │
 │              │                    │                    │  [hint_count=2]   │                   │
 │              │                    │                    │<───────────────────│                   │
 │              │                    │                    │ calculateFinalCost()                   │
 │              │                    │                    │  cost = 120 * (1 - 40%) = 72 SP      │
 │              │                    │                    │<───────────────────│                   │
 │              │                    │ [final_cost]      │                    │                   │
 │              │                    │<────────────────│                    │                   │
 │              │                    │ Validate SP funds  │                    │                   │
 │              │                    │ INSERT skill_acquisition              │
 │              │                    │────────────────────────────────────────────────────────>│
 │              │                    │ CHECK if Normal skill has evolution path                 │
 │              │                    │  [skill: "Go with the Flow"]                           │
 │              │                    │  [evolution: "Lane Legerdemain"]                       │
 │              │                    │ Trigger SkillAcquired event                             │
 │              │ Skill + Cost       │                    │                    │                   │
 │              │<──────────────────│                    │                    │                   │
 │ [Show Summary]│                    │                    │                    │                   │
 │<─────────────│                    │                    │                    │                   │
 │              │ [Option: Evolve to Rare if available]   │                    │                   │
 │ [Evolve?]    │                    │                    │                    │                   │
 │─────────────>│ POST /skills/{id}/evolve                │                    │                   │
 │              │──────────────────────────────────────>│                    │                   │
 │              │                    │ evolveSkill()     │                    │                   │
 │              │                    │────────────────>│                    │                   │
 │              │                    │  REMOVE old skill │                    │                   │
 │              │                    │  INSERT new skill │                    │                   │
 │              │                    │────────────────────────────────────────────────────────>│
 │              │ Evolved skill info │                    │                    │                   │
 │              │<──────────────────│                    │                    │                   │
 │ [Complete]   │                    │                    │                    │                   │
 │<─────────────│                    │                    │                    │                   │
```

**Duration**: ~400ms  
**Key Decision Points**: Hint count → Final cost calculation → Evolution check

---

## SD-005: Training Prediction & Recommendation (with AI)

```
User         PredictionUI      PredictionController     PredictionService    OllamaService     Cache
 │               │                     │                       │                   │              │
 │ [Request]     │                     │                       │                   │              │
 │──────────────>│                     │                       │                   │              │
 │               │ GET /predictions    │                       │                   │              │
 │               │────────────────────>│                       │                   │              │
 │               │                     │ getPredictions()      │                   │              │
 │               │                     │──────────────────────>│                   │              │
 │               │                     │                       │ [Check cache]    │              │
 │               │                     │                       │───────────────────────────────>│
 │               │                     │                       │  [Cache MISS]                  │
 │               │                     │                       │<───────────────────────────────│
 │               │                     │                       │ calculateGains() x5 facilities│
 │               │                     │                       │  [Speed, Stamina, Power...]  │
 │               │                     │                       │ rankOptions()                 │
 │               │                     │                       │  [Score: 92.5, 88.3, 82.1...]│
 │               │                     │                       │ Store in cache (5 min TTL)    │
 │               │                     │                       │───────────────────────────────>│
 │               │                     │ [predictions]         │                   │              │
 │               │                     │<──────────────────────│                   │              │
 │               │ Ranked predictions  │                       │                   │              │
 │               │<────────────────────│                       │                   │              │
 │ [Show Options]│                     │                       │                   │              │
 │<──────────────│                     │                       │                   │              │
 │               │                     │                       │                   │              │
 │ [Request AI]  │                     │                       │                   │              │
 │──────────────>│                     │                       │                   │              │
 │               │ GET /ai-recommendation                      │                   │              │
 │               │────────────────────>│                       │                   │              │
 │               │                     │ generateRecommendation()                 │              │
 │               │                     │──────────────────────>│ generateCompletion()           │
 │               │                     │                       │──────────────────────────────>│
 │               │                     │                       │  [Complex reasoning]          │
 │               │                     │                       │  [2-3 second latency]        │
 │               │                     │                       │<──────────────────────────────│
 │               │                     │ [recommendation]      │                   │              │
 │               │                     │<──────────────────────│                   │              │
 │               │ AI recommendation   │                       │                   │              │
 │               │<────────────────────│                       │                   │              │
 │ [View Advice] │                     │                       │                   │              │
 │<──────────────│                     │                       │                   │              │
```

**Duration**: ~200ms (cached) or ~2.5s (with AI)  
**Optimization**: Cache for instant response, AI as optional deep analysis

---

## SD-006: External API Data Sync with Fallback

```
System              ExternalAPIService    CircuitBreaker    PrimaryAPI      FallbackAPI    Cache    Database
 │                  │                     │                 │               │              │        │
 │ [Scheduled Sync] │                     │                 │               │              │        │
 │─────────────────>│                     │                 │               │              │        │
 │                  │ fetchCharacterData()│                 │               │              │        │
 │                  │────────────────────>│                 │               │              │        │
 │                  │                     │ [Check state]   │               │              │        │
 │                  │                     │ [CLOSED]        │               │              │        │
 │                  │                     │ allowRequest()  │               │              │        │
 │                  │                     │──────────────────────────────>│               │        │
 │                  │                     │                 │ [Response OK]│               │        │
 │                  │                     │                 │              │               │        │
 │                  │                     │<──────────────────────────────│               │        │
 │                  │                     │ updateState()   │               │              │        │
 │                  │                     │ [CLOSED]        │               │              │        │
 │                  │ [character_data]    │                 │               │              │        │
 │                  │<────────────────────│                 │               │              │        │
 │                  │ Cache data (24hrs)  │                 │               │              │        │
 │                  │────────────────────────────────────────────────────────────────────────────>│
 │                  │ Store to database   │                 │               │              │        │
 │                  │────────────────────────────────────────────────────────────────────────────>│
 │                  │                     │                 │               │              │        │
 │  [Later Request] │ [API DOWN scenario]│                 │               │              │        │
 │─────────────────>│ fetchCharacterData()│                 │               │              │        │
 │                  │────────────────────>│                 │               │              │        │
 │                  │                     │ [Check state]   │               │              │        │
 │                  │                     │ [OPEN - failures exceeded]       │              │        │
 │                  │                     │ return cached_response()        │              │        │
 │                  │                     │<─────────────────────────────────────────────────────>│
 │                  │ [cached_data]       │                 │               │              │        │
 │                  │<────────────────────│                 │               │              │        │
 │  [Data returned] │                     │                 │               │              │        │
 │<─────────────────│                     │                 │               │              │        │
 │                  │                     │ [Background: retry check]       │              │        │
 │                  │                     │ [HALF_OPEN after 60s]          │              │        │
 │                  │                     │──────────────────────────────>│               │        │
 │                  │                     │                 │ [Recovery OK] │               │        │
 │                  │                     │                 │              │               │        │
 │                  │                     │                 │<──────────────│               │        │
 │                  │                     │ setState(CLOSED)│               │              │        │
 │                  │                     │ [Circuit re-closed]            │              │        │
```

**Duration**: ~100ms (cache) or ~5s (timeout)  
**Resilience**: Circuit breaker prevents cascading failures, intelligent fallback

---

## SD-007: WebSocket Real-time Character Update Broadcast

```
CharacterService    WebSocketService    UserA Connection    UserB Connection    UserC Connection
    │                    │                    │                   │                   │
    │ updateCharacter()  │                    │                   │                   │
    │─────────────────>│                    │                   │                   │
    │                    │ broadcast()        │                   │                   │
    │                    │ channel: character.{id}               │                   │
    │                    │                    │                   │                   │
    │                    │ [Real-time event] │                   │                   │
    │                    │───────────────────────────────────────────────────────>│
    │                    │                    │ stats: [...]       │                   │
    │                    │                    │ mood: Good          │                   │
    │                    │                    │ energy: 78          │                   │
    │                    │                    │ timestamp: now()    │                   │
    │                    │                    │                   │                   │
    │                    │ [Event dispatched] │                   │                   │
    │                    │───────────────────>│ [Event received] │ [Event received] │
    │                    │ (to all subscribers)│                   │                   │
    │                    │                    │ [UI updates]       │ [UI updates]      │
    │                    │                    │ [Animation]        │ [Animation]       │
    │                    │                    │ [Sound]            │ [Sound]           │
    │                    │                    │                   │                   │
```

**Duration**: <100ms per connection  
**Scalability**: Optimized for multiple concurrent connections via Laravel Reverb

---

## Summary

**Total Sequences**: 7 critical flows  
**Coverage**:

- Character Management: SD-001
- Training & Sessions: SD-002, SD-005
- Race System: SD-003
- Skill Management: SD-004
- External Integration: SD-006
- Real-time Updates: SD-007

**Key Insights**:

1. Parallel operations reduce latency
2. Caching critical for responsiveness
3. Event-driven architecture enables loose coupling
4. Fallback mechanisms ensure resilience
5. WebSocket enables real-time collaboration

---

**Related**: [TECH-FLOW Index](../tech-flow/000_TECH_FLOW_INDEX.md), [SPEC Index](../specs/000_SPECS_INDEX.md)
