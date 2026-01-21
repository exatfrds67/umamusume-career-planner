# FLOW-007: External Integration System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Related Documents**: [PRD-007], [SPEC-007]

---

## 1. API Integration & Fallback Flow

```mermaid
flowchart TD
    Start([Request External Data]) --> BuildKey[Build Cache Key]
    BuildKey --> CheckCache{Cache Hit?}
    CheckCache -->|Hit| ReturnCache[Return cached data]
    CheckCache -->|Miss| CallPrimary[Call umapyoi.net]
    CallPrimary --> PrimaryOK{Success?}
    PrimaryOK -->|Yes| ValidatePrimary[Validate schema]
    PrimaryOK -->|No| CallSecondary[Call UmamusumeDB]
    ValidatePrimary --> CacheStore[Store fresh + stale]
    CacheStore --> ReturnData[Return data]
    CallSecondary --> SecondaryOK{Success?}
    SecondaryOK -->|Yes| ValidateSecondary[Validate schema]
    SecondaryOK -->|No| TryStale{Stale cache?}
    ValidateSecondary --> CacheStore
    TryStale -->|Yes| ReturnStale[Return stale with warning]
    TryStale -->|No| ReturnError[Return error]
```

---

## 2. OCR Screenshot Analysis Flow

```mermaid
flowchart TD
    Start([Upload Image]) --> ValidateImage{PNG/JPG <=10MB?}
    ValidateImage -->|No| Reject
    ValidateImage -->|Yes| Preprocess[OpenCV preprocess]
    Preprocess --> RunOCR[Tesseract jpn psm6]
    RunOCR --> PostProcess[Clean text per field]
    PostProcess --> ScoreConfidence[Score confidence]
    ScoreConfidence --> LowConf{<70%?}
    LowConf -->|Yes| QueueReview[Queue manual review]
    LowConf -->|No| BuildResult
    QueueReview --> BuildResult
    BuildResult --> ReturnUser[Return text + confidence]
```

---

## 3. Data Synchronization Flow

```mermaid
flowchart TD
    Start([Trigger Sync]) --> DetermineTrigger{Daily/Manual/Update}
    DetermineTrigger --> QueueJob[Queue Sync Job]
    QueueJob --> SyncTrainees
    SyncTrainees --> SyncSkills
    SyncSkills --> SyncCards
    SyncCards --> SyncRaces
    SyncRaces --> SyncFactors
    SyncFactors --> CompileReport
    CompileReport --> InvalidateCaches[Invalidate reference caches]
    InvalidateCaches --> Finish
```

---

## 4. Rate Limiting & Throttling Flow

```mermaid
flowchart TD
    Start([API Request]) --> IdentifyAPI{Primary or Fallback}
    IdentifyAPI --> CheckLimit[Check limit in Redis]
    CheckLimit -->|Over| Strategy{Wait/Fallback/Error}
    Strategy -->|Wait| Sleep
    Strategy -->|Fallback| Switch
    Strategy -->|Error| Return429
    CheckLimit -->|Under| Proceed
    Proceed --> Track[Track request metrics]
```

---

## 5. Error Handling & Retry Logic Flow

```mermaid
flowchart TD
    Start([External Call]) --> CallAPI
    CallAPI --> Status{Status}
    Status -->|200| Success
    Status -->|4xx| LogNoRetry[Log client error]
    Status -->|429| RateLimit[Handle rate limit]
    Status -->|5xx/Timeout| RetryCheck{Retries <3?}
    RetryCheck -->|Yes| BackoffRetry
    RetryCheck -->|No| FallbackCheck{Fallback?}
    FallbackCheck -->|Yes| CallFallback
    FallbackCheck -->|No| FinalError
    BackoffRetry --> CallAPI
    CallFallback --> Status
```

---

## 6. Cache Management Flow

```mermaid
flowchart TD
    Start([Cache Access]) --> Op{Get/Set/Invalidate}
    Op -->|Get| GetFlow
    Op -->|Set| SetFlow
    Op -->|Invalidate| InvalidateFlow

    subgraph GetFlow
        BuildKey --> CheckRedis{Hit?}
        CheckRedis -->|Hit| TTLCheck{Expired?}
        TTLCheck -->|No| ReturnData
        TTLCheck -->|Yes| RemoveExpired
        CheckRedis -->|Miss| Miss
    end

    subgraph SetFlow
        PrepareData --> TTLByType[TTL by resource]
        TTLByType --> StoreFresh[Set fresh]
        StoreFresh --> StoreStale[Set stale backup]
    end

    subgraph InvalidateFlow
        BuildPattern --> DeleteKeys[Delete matching keys]
    end
```

---

## 7. Monitoring & Alerting Flow

```mermaid
flowchart TD
    Start([Monitor]) --> CollectMetrics[API/Cache/OCR/Sync]
    CollectMetrics --> Analyze[Detect anomalies]
    Analyze --> Alert{Severity}
    Alert -->|Critical| NotifyOncall
    Alert -->|Warning| NotifyTeam
    Alert -->|Info| LogOnly
    NotifyOncall --> Dashboard
    NotifyTeam --> Dashboard
    LogOnly --> Dashboard[Update dashboard]
```

---

**Phase 3A Restored**: All 7 flow diagrams recreated.
