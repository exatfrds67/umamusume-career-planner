# SEQ-013: Achievement Unlock

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.0.0  
**Date**: January 24, 2026  
**Related Documents**: [PRD-001], [SPEC-001], [FLOW-001]

---

## Table of Contents

1. [Overview](#1-overview)
2. [Participants](#2-participants)
3. [Sequence Flow](#3-sequence-flow)
4. [Detailed Interactions](#4-detailed-interactions)
5. [Data Structures](#5-data-structures)
6. [Error Handling](#6-error-handling)
7. [Performance Considerations](#7-performance-considerations)
8. [Related Documentation](#8-related-documentation)

---

## 1. Overview

### 1.1 Purpose

This sequence diagram documents the achievement unlock workflow in the Umamusume Career Planner application, covering trigger evaluation, atomic unlock operations, reward granting, and user notification.

### 1.2 Scope

**Covers:**

- Real-time achievement trigger evaluation
- Atomic unlock and reward granting with transaction safety
- Audit logging for achievement unlocks
- User notification via multiple channels
- Achievement history and progress tracking

**Related Artifacts:**

- PRD: [PRD-001](../prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001](../specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001](../flows/FLOW-001_Character_Management_System.md)

### 1.3 Business Context

The achievement system enables:

- Recognition of player milestones and accomplishments
- Reward distribution for career progression
- Engagement tracking and analytics
- Gamification of the planning experience

**Success Criteria:**

- Trigger evaluation within 100ms
- Atomic unlock operations with rollback safety
- Reward granting without duplication
- User notification within 200ms

---

## 2. Participants

### 2.1 System Components

| Component | Type | Responsibility |
|-----------|------|----------------|
| **Event System** | Infrastructure | Dispatches career/training/race events |
| **AchievementEngine** | Domain Service | Evaluates triggers and criteria |
| **AchievementRepository** | Infrastructure | Achievement persistence |
| **RewardService** | Domain Service | Reward calculation and granting |
| **NotificationService** | Infrastructure | User notification delivery |
| **AuditLogger** | Infrastructure | Achievement unlock audit trail |
| **Database** | Infrastructure | MySQL/MariaDB persistence layer |
| **Cache** | Infrastructure | Redis achievement cache |

### 2.2 Component Locations

```

app/
├── Services/
│   ├── AchievementEngine.php
│   ├── RewardService.php
│   └── NotificationService.php
├── Repositories/
│   └── AchievementRepository.php
├── Events/
│   ├── CareerCompleted.php
│   ├── StatMilestoneReached.php
│   ├── RaceWon.php
│   └── SkillAcquired.php
└── Models/
    ├── Achievement.php
    ├── AchievementUnlock.php
    └── Reward.php

```

---

## 3. Sequence Flow

### 3.1 High-Level Flow Diagram

```mermaid
sequenceDiagram
    actor User
    participant Event as System Event
    participant Engine as AchievementEngine
    participant Repo as AchievementRepository
    participant RewardSvc as RewardService
    participant NotifSvc as NotificationService
    participant Audit as AuditLogger
    participant DB as Database
    participant Cache as Redis Cache

    Note over User,Cache: ACHIEVEMENT TRIGGER
    User->>Event: Perform action (race win, stat milestone, etc.)
    Event->>Engine: Dispatch event
    Engine->>Engine: Identify affected users
    Engine->>Cache: Check cached achievement state
    
    alt Cache Hit
        Cache-->>Engine: Cached achievement data
    else Cache Miss
        Engine->>DB: Load user achievements
        DB-->>Engine: Achievement data
        Engine->>Cache: Store achievement state
    end
    
    Engine->>Engine: Evaluate triggers against event
    
    alt No Achievements Unlocked
        Engine-->>Event: No unlocks
    else Achievements Unlocked
        Engine->>DB: BEGIN TRANSACTION
        
        loop For each unlocked achievement
            Engine->>Repo: Create unlock record
            Repo->>DB: INSERT achievement_unlocks
            DB-->>Repo: Unlock ID
            
            Engine->>RewardSvc: Calculate rewards
            RewardSvc->>RewardSvc: Determine reward type and amount
            RewardSvc->>DB: Grant rewards (SP, items, currency)
            DB-->>RewardSvc: Reward granted
            
            Engine->>Audit: Log achievement unlock
            Audit->>DB: INSERT audit_log
        end
        
        Engine->>DB: COMMIT TRANSACTION
        
        Engine->>NotifSvc: Queue notifications
        NotifSvc->>NotifSvc: Build notification payload
        NotifSvc-->>User: Display in-app notification
        NotifSvc-->>User: Optional email notification
        
        Engine->>Cache: Invalidate achievement cache
        Cache-->>Engine: Cache cleared
        
        Engine-->>Event: Unlock complete
    end
```

### 3.2 Timeline Breakdown

| Phase | Duration | Description |
|-------|----------|-------------|
| **Event Dispatch** | ~10ms | System event triggered |
| **Cache Check** | ~20ms | Check cached achievement state |
| **Trigger Evaluation** | ~50ms | Evaluate achievement criteria |
| **Database Lock** | ~30ms | Row-level locking for atomicity |
| **Unlock Creation** | ~50ms | Create unlock records |
| **Reward Granting** | ~100ms | Calculate and grant rewards |
| **Audit Logging** | ~40ms | Record unlock in audit log |
| **Notification Queue** | ~30ms | Queue user notifications |
| **Cache Invalidation** | ~20ms | Clear achievement cache |
| **Total** | ~350ms | Complete unlock flow |

---

## 4. Detailed Interactions

### 4.1 Achievement Engine

**Request Flow:**

```
System Event → AchievementEngine → Trigger Evaluation → Unlock Execution
```

**Service Implementation:**

```php
// AchievementEngine.php
class AchievementEngine
{
    public function __construct(
        private AchievementRepository $repository,
        private RewardService $rewardService,
        private NotificationService $notificationService,
        private AuditLogger $auditLogger,
        private CacheManager $cache,
    ) {}
    
    public function evaluate(SystemEvent $event): void
    {
        $users = $this->identifyAffectedUsers($event);
        
        foreach ($users as $user) {
            $this->evaluateForUser($user, $event);
        }
    }
    
    private function evaluateForUser(User $user, SystemEvent $event): void
    {
        // 1. Load user's current achievement state
        $state = $this->loadAchievementState($user);
        
        // 2. Identify potential unlocks
        $triggers = $this->getTriggersForEvent($event);
        $newUnlocks = [];
        
        foreach ($triggers as $trigger) {
            if ($this->isMet($trigger, $event, $state)) {
                $newUnlocks[] = $trigger->achievement_id;
            }
        }
        
        // 3. Process unlocks atomically
        if (!empty($newUnlocks)) {
            $this->processUnlocks($user, $newUnlocks, $event);
        }
    }
    
    private function isMet(AchievementTrigger $trigger, SystemEvent $event, array $state): bool
    {
        // Already unlocked?
        if (in_array($trigger->achievement_id, $state['unlocked_ids'])) {
            return false;
        }
        
        // Evaluate trigger conditions
        return match ($trigger->type) {
            'stat_milestone' => $this->checkStatMilestone($trigger, $event),
            'race_win' => $this->checkRaceWin($trigger, $event),
            'skill_count' => $this->checkSkillCount($trigger, $event),
            'career_complete' => $this->checkCareerComplete($trigger, $event),
            'consecutive_wins' => $this->checkConsecutiveWins($trigger, $event),
            default => false,
        };
    }
    
    private function processUnlocks(User $user, array $achievementIds, SystemEvent $event): void
    {
        DB::transaction(function () use ($user, $achievementIds, $event) {
            foreach ($achievementIds as $achievementId) {
                // 1. Create unlock record
                $unlock = $this->repository->createUnlock([
                    'user_id' => $user->id,
                    'achievement_id' => $achievementId,
                    'unlocked_at' => now(),
                    'trigger_event' => get_class($event),
                    'event_data' => $event->toArray(),
                ]);
                
                // 2. Grant rewards
                $achievement = Achievement::find($achievementId);
                $this->rewardService->grantRewards($user, $achievement->rewards);
                
                // 3. Audit log
                $this->auditLogger->log('achievement_unlock', $user->id, [
                    'achievement_id' => $achievementId,
                    'achievement_name' => $achievement->name,
                    'rewards' => $achievement->rewards,
                    'trigger' => get_class($event),
                ]);
            }
            
            // 4. Queue notifications
            $this->notificationService->notifyAchievementUnlocks($user, $achievementIds);
            
            // 5. Invalidate cache
            $this->cache->forget("achievements.user.{$user->id}");
        });
    }
}
```

### 4.2 Trigger Evaluation Examples

#### Stat Milestone Trigger

```php
private function checkStatMilestone(AchievementTrigger $trigger, SystemEvent $event): bool
{
    if (!$event instanceof StatMilestoneReached) {
        return false;
    }
    
    $criteria = $trigger->criteria;
    
    return $event->stat === $criteria['stat'] &&
           $event->value >= $criteria['threshold'];
}
```

#### Race Win Trigger

```php
private function checkRaceWin(AchievementTrigger $trigger, SystemEvent $event): bool
{
    if (!$event instanceof RaceWon) {
        return false;
    }
    
    $criteria = $trigger->criteria;
    
    // Check grade requirement
    if (isset($criteria['grade']) && $event->race->grade !== $criteria['grade']) {
        return false;
    }
    
    // Check placement requirement
    if (isset($criteria['placement']) && $event->placement > $criteria['placement']) {
        return false;
    }
    
    return true;
}
```

#### Skill Count Trigger

```php
private function checkSkillCount(AchievementTrigger $trigger, SystemEvent $event): bool
{
    if (!$event instanceof SkillAcquired) {
        return false;
    }
    
    $criteria = $trigger->criteria;
    $skillCount = SkillAcquisition::where('career_id', $event->career->id)
        ->where('status', 'acquired')
        ->count();
    
    return $skillCount >= $criteria['count'];
}
```

### 4.3 Reward Service

**Reward Granting:**

```php
// RewardService.php
class RewardService
{
    public function grantRewards(User $user, array $rewards): void
    {
        foreach ($rewards as $reward) {
            match ($reward['type']) {
                'skill_points' => $this->grantSkillPoints($user, $reward['amount']),
                'currency' => $this->grantCurrency($user, $reward['amount']),
                'item' => $this->grantItem($user, $reward['item_id'], $reward['quantity']),
                'support_card' => $this->grantSupportCard($user, $reward['card_id']),
            };
        }
    }
    
    private function grantSkillPoints(User $user, int $amount): void
    {
        // Grant SP to user's active career
        $activeCareers = Career::where('user_id', $user->id)
            ->where('status', CareerStatus::InProgress)
            ->get();
        
        foreach ($activeCareers as $career) {
            $career->increment('total_sp_available', $amount);
        }
    }
    
    private function grantCurrency(User $user, int $amount): void
    {
        $user->increment('currency', $amount);
    }
    
    private function grantItem(User $user, int $itemId, int $quantity): void
    {
        $inventory = $user->inventory()->firstOrCreate(['user_id' => $user->id]);
        
        $items = $inventory->items ?? [];
        $items[$itemId] = ($items[$itemId] ?? 0) + $quantity;
        
        $inventory->update(['items' => $items]);
    }
}
```

### 4.4 Notification Delivery

**Notification Service:**

```php
// NotificationService.php
class NotificationService
{
    public function notifyAchievementUnlocks(User $user, array $achievementIds): void
    {
        $achievements = Achievement::whereIn('id', $achievementIds)->get();
        
        foreach ($achievements as $achievement) {
            // 1. In-app notification
            Notification::create([
                'user_id' => $user->id,
                'type' => 'achievement_unlock',
                'title' => 'Achievement Unlocked!',
                'message' => $achievement->name,
                'data' => [
                    'achievement_id' => $achievement->id,
                    'icon' => $achievement->icon,
                    'rewards' => $achievement->rewards,
                ],
            ]);
            
            // 2. WebSocket real-time notification
            broadcast(new AchievementUnlocked($user, $achievement));
            
            // 3. Optional email notification
            if ($user->preferences['email_achievements'] ?? false) {
                Mail::to($user)->queue(new AchievementUnlockedEmail($achievement));
            }
        }
    }
}
```

### 4.5 Achievement State Caching

**Cache Strategy:**

```php
private function loadAchievementState(User $user): array
{
    $cacheKey = "achievements.user.{$user->id}";
    
    return $this->cache->remember($cacheKey, 3600, function () use ($user) {
        $unlocked = AchievementUnlock::where('user_id', $user->id)
            ->pluck('achievement_id')
            ->toArray();
        
        $progress = $this->calculateProgress($user);
        
        return [
            'unlocked_ids' => $unlocked,
            'progress' => $progress,
        ];
    });
}

private function calculateProgress(User $user): array
{
    $careers = Career::where('user_id', $user->id)->get();
    
    return [
        'total_stats' => $careers->sum(fn($c) => $c->speed + $c->stamina + $c->power + $c->guts + $c->wit),
        'careers_completed' => $careers->where('status', CareerStatus::Completed)->count(),
        'g1_wins' => RaceResult::where('user_id', $user->id)
            ->where('grade', 'G1')
            ->where('placement', 1)
            ->count(),
        'skills_acquired' => SkillAcquisition::whereIn('career_id', $careers->pluck('id'))
            ->where('status', 'acquired')
            ->count(),
    ];
}
```

---

## 5. Data Structures

### 5.1 Achievement Model

```json
{
  "id": 1,
  "name": "Speed Demon",
  "description": "Reach 1000 Speed stat",
  "icon": "speed_icon.png",
  "rarity": "rare",
  "category": "stat_milestone",
  "requirements": {
    "stat": "speed",
    "threshold": 1000
  },
  "rewards": [
    {
      "type": "skill_points",
      "amount": 50
    },
    {
      "type": "currency",
      "amount": 1000
    }
  ],
  "created_at": "2026-01-20T10:00:00Z"
}
```

### 5.2 Achievement Unlock Model

```json
{
  "id": 42,
  "user_id": 1,
  "achievement_id": 1,
  "unlocked_at": "2026-01-24T10:30:00Z",
  "trigger_event": "App\\Events\\StatMilestoneReached",
  "event_data": {
    "career_id": 157,
    "stat": "speed",
    "value": 1000,
    "turn_number": 45
  }
}
```

### 5.3 Achievement Trigger Configuration

```json
{
  "id": 1,
  "achievement_id": 1,
  "type": "stat_milestone",
  "criteria": {
    "stat": "speed",
    "threshold": 1000
  },
  "is_active": true
}
```

### 5.4 Notification Payload

```json
{
  "type": "achievement_unlock",
  "title": "Achievement Unlocked!",
  "message": "Speed Demon",
  "icon": "speed_icon.png",
  "data": {
    "achievement_id": 1,
    "rewards": [
      {
        "type": "skill_points",
        "amount": 50
      },
      {
        "type": "currency",
        "amount": 1000
      }
    ]
  },
  "timestamp": "2026-01-24T10:30:00Z"
}
```

---

## 6. Error Handling

### 6.1 Validation Errors

| Error Code | Condition | HTTP Status | User Message |
|------------|-----------|-------------|--------------|
| `ACH_001` | Achievement not found | 404 | "Achievement not found" |
| `ACH_002` | Already unlocked | 409 | "Achievement already unlocked" |
| `ACH_003` | Invalid trigger criteria | 422 | "Invalid achievement criteria" |
| `ACH_004` | Reward granting failed | 500 | "Failed to grant rewards" |

### 6.2 Error Recovery Flow

```mermaid
sequenceDiagram
    participant Engine as AchievementEngine
    participant DB as Database
    participant Audit as AuditLogger
    
    Engine->>DB: BEGIN TRANSACTION
    Engine->>DB: Create unlock record
    
    alt Database Error
        DB-->>Engine: Constraint violation
        Engine->>DB: ROLLBACK
        Engine->>Audit: Log error
        Engine-->>Engine: Return error
    else Reward Error
        Engine->>Engine: Grant rewards
        Engine-->>Engine: Reward service error
        Engine->>DB: ROLLBACK
        Engine->>Audit: Log error
        Engine-->>Engine: Return error
    else Success
        Engine->>DB: COMMIT
        Engine->>Audit: Log success
        Engine-->>Engine: Return success
    end
```

### 6.3 Duplicate Detection

**Prevention Strategy:**

```php
// Check for existing unlock before processing
$existingUnlock = AchievementUnlock::where('user_id', $user->id)
    ->where('achievement_id', $achievementId)
    ->exists();

if ($existingUnlock) {
    Log::warning('Duplicate achievement unlock attempt', [
        'user_id' => $user->id,
        'achievement_id' => $achievementId,
    ]);
    return; // Skip unlock
}
```

---

## 7. Performance Considerations

### 7.1 Performance Metrics

| Operation | Target | Current | Status |
|-----------|--------|---------|--------|
| Trigger evaluation | <50ms | ~40ms | ✅ Met |
| Unlock creation (batch) | <100ms | ~85ms | ✅ Met |
| Reward granting | <100ms | ~80ms | ✅ Met |
| Notification delivery | <50ms | ~35ms | ✅ Met |
| Cache lookup | <10ms | ~5ms | ✅ Met |
| Total unlock flow | <350ms | ~300ms | ✅ Met |

### 7.2 Optimization Strategies

**Implemented:**

- Achievement state caching (1-hour TTL)
- Batch unlock processing within single transaction
- Async notification delivery via queue
- Indexed queries for unlock checks

**Code Example:**

```php
// Optimized batch unlock check
$existingUnlocks = AchievementUnlock::where('user_id', $userId)
    ->whereIn('achievement_id', $achievementIds)
    ->pluck('achievement_id')
    ->toArray();

$newUnlocks = array_diff($achievementIds, $existingUnlocks);
```

### 7.3 Database Query Analysis

**Query Count for Unlock Flow:**

- Cache check: 0 queries (Redis)
- Achievement state load: 1 query (cached)
- Unlock creation: N queries (1 per achievement)
- Reward granting: M queries (1 per reward type)
- Audit logging: 1 query (insert)

**Total Queries:** 2 + N + M queries (where N = unlocks, M = reward types)

**Index Usage:**

```sql
-- Critical indexes for achievement system
CREATE INDEX idx_achievement_unlocks_user ON ucp_achievement_unlocks(user_id, achievement_id);
CREATE INDEX idx_achievement_unlocks_created ON ucp_achievement_unlocks(created_at DESC);
CREATE INDEX idx_achievements_category ON ucp_achievements(category, is_active);
```

### 7.4 Cache Strategy

**Cache Keys:**

- Achievement state: `achievements.user.{user_id}`
- Achievement definitions: `achievements.all`
- TTL: 1 hour for state, indefinite for definitions

**Cache Invalidation:**

```php
// Invalidate on unlock
Cache::forget("achievements.user.{$userId}");

// Invalidate on achievement update (admin)
Cache::forget('achievements.all');
```

---

## 8. Related Documentation

### 8.1 System Documentation

| Document | Description |
|----------|-------------|
| [PRD-001](../prds/PRD-001_Character_Management.md) | Product requirements for character management |
| [SPEC-001](../specs/SPEC-001_Character_Management_Technical.md) | Technical specification for character system |
| [FLOW-001](../flows/FLOW-001_Character_Management_System.md) | System flow for character operations |

### 8.2 Related Sequences

| Sequence | Description |
|----------|-------------|
| [SEQ-001](SEQ-001_Character_Creation_Sequence.md) | Character creation (initial achievements) |
| [SEQ-002](SEQ-002_Training_Block_Resolution.md) | Training completion (stat achievements) |
| [SEQ-004](SEQ-004_Race_Registration_and_Outcome.md) | Race completion (race achievements) |

### 8.3 Database Documentation

| Document | Description |
|----------|-------------|
| [DBD-009](../009_DBD_Database_Documentation.md) | Complete database schema documentation |

---

## Document Control

### Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.0.0 | 2026-01-24 | Development Team | Complete rewrite aligned with v2.0.0 implementation; added detailed sequence flows, trigger evaluation, reward granting, performance metrics, and aligned with current Laravel 12 architecture |
| 1.0.0 | 2026-01-14 | Development Team | Initial draft |

### Approval

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Technical Lead | | | |
| QA Lead | | | |

### Review Schedule

- Next Review: 2026-04-24
- Review Frequency: Quarterly or on major feature changes

---

**Related Standards:**

- Laravel 12 Best Practices
- PSR-12 Coding Standards
- Mermaid Diagram Standards
- IEEE 830 SRS Format

---

*This sequence diagram reflects the current implementation of the achievement unlock workflow as of v2.0.0. For the most up-to-date information, refer to the source code in `app/Services/AchievementEngine.php`, `app/Services/RewardService.php`, and related files.*
