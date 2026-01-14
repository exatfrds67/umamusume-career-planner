# SEQ-008: Notification Delivery

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-008]

---

## Sequence Overview
- Sends training/race reminders via in-app and email; respects user preferences and quiet hours.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant CRON as Scheduler
    participant API as Backend
    participant DB as Database
    participant M as Mailer
    participant P as PushService

    CRON->>API: Trigger notification job
    API->>DB: Fetch pending notifications + prefs
    DB-->>API: Batch

    loop For each notification
        API->>API: Check quiet hours & channels
        alt Email
            API->>M: SendMail(to, template)
            M-->>API: Delivered/queued
        end
        alt In-app/Push
            API->>P: SendPush(user, payload)
            P-->>API: Delivered/queued
        end
        API->>DB: Mark sent/log status
    end
    API-->>CRON: Job complete
```
