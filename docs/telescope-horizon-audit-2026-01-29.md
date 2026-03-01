# Telescope & Horizon Audit Report

**Document Version**: 1.0.0  
**Date**: 2026-01-29  
**Project**: UmamusumeCareerPlanner  
**Status**: Complete

---

## Table of Contents

1. [Overview](#overview)
2. [Telescope Tab Audit](#telescope-tab-audit)
3. [Horizon Tab Audit](#horizon-tab-audit)
4. [Issues Found & Resolutions](#issues-found--resolutions)
5. [Outstanding Items](#outstanding-items)

---

## Overview

A thorough tab-by-tab audit of the Laravel Telescope (`/telescope`) and Laravel Horizon
(`/horizon`) monitoring dashboards was performed. The application is running on XAMPP
(Windows) with Redis 7.0.15 in WSL2 for queue and cache operations.

---

## Telescope Tab Audit

### Requests

| Status | Finding |
| --- | --- |
| ✅ Normal | GET/POST traffic across all admin and public routes |
| ℹ️ Info | One 404 occurs when navigating to `/admin/telescope` (not a real admin route) |

### Commands

| Status | Finding |
| --- | --- |
| ✅ Normal | `list`, `boost:execute-tool`, `test` all exit with code 0 |

### Schedule

| Status | Finding |
| --- | --- |
| ⚠️ Empty | No scheduled tasks have been recorded — likely no tasks are defined yet |

### Jobs

| Status | Finding |
| --- | --- |
| ⚠️ Empty | No queued jobs have been dispatched during the audit window |

### Batches

| Status | Finding |
| --- | --- |
| ✅ Empty | Expected — no batch operations in use |

### Cache

| Status | Finding |
| --- | --- |
| ⚠️ Misses | `apm:alerts:list` shows constant **misses** — expected; key only written when an alert is generated |
| ⚠️ Misses | `query_optimization:stats` shows constant **misses** — expected; key only written when stats are persisted |
| ✅ Hits | `api_perf:overview` shows healthy hit/miss ratio |

**Root Cause**: Both keys read with a `[]` default. Until the first alert is triggered or query stats
are persisted, every read is a miss. This increases the "miss" count in Telescope and lowers the
Redis Keyspace Hit Rate metric but is **not a bug**.

### Dumps

| Status | Finding |
| --- | --- |
| ✅ Clean | No stray `dump()` or `dd()` calls found |

### Events

| Status | Finding |
| --- | --- |
| ℹ️ Normal | Only `cache:cleared` and `cache:clearing` events recorded |
| ℹ️ Info | All events show **0 Listeners** — expected for built-in cache events |

### Exceptions

See [Issues Found & Resolutions](#issues-found--resolutions) for full detail.

| Severity | Exception | Status |
| --- | --- | --- |
| 🟡 Historical | `Undefined array key "jobs_count"` in queue view | Stale compiled cache — no bug in current code |
| 🟡 Historical | `Collection::total does not exist` in support-cards view | Historical — `$cards` now uses `->paginate()` |
| 🟡 Historical | `Undefined variable $races` in dashboard view | Historical — controller always passes `$races` |
| 🟠 Dev Tool | `ParseErrorException` T_NS_SEPARATOR | Tinker/demo usage, not production |
| 🟠 Test | `CommandNotFoundException` for `--compact` | Running `route:list --compact` (invalid option) |
| 🟡 Historical | `QueryException` table already exists | Re-running migrations on existing DB |
| ✅ Resolved | `RedisException` No connection (212×) | Pre-Redis fix (1+ day ago); all resolved |
| ℹ️ Expected | `TypeError` Anthropic key must be string | No AWS credentials configured |
| ✅ Resolved | `BadMethodCallException` TrainingController::index | Old missing route (3+ days ago) |

### Gates

| Status | Finding |
| --- | --- |
| ✅ Normal | Mostly `allowed`; one `denied` matching expected auth behavior |

### HTTP Client

| Status | Finding |
| --- | --- |
| ⚠️ N/A | `https://api.umamusumedb.com/health` returns **N/A** (unreachable) |
| ⚠️ Slow | `api.umapyoi.net` calls averaging **1000–1400ms** (external API latency) |

**Note**: `api.umamusumedb.com` appears to be offline or blocking requests. The external API
latency from `api.umapyoi.net` is inherent to the third-party service and not actionable.

### Logs

| Status | Finding |
| --- | --- |
| ⚠️ Expected | All `error` level logs are AI/Bedrock-related (2–3 days ago) |
| ℹ️ Expected | `[Bedrock] AWS Error`, `[AgentRouting] Primary/Fallback provider failed` — no credentials |

### Mail

| Status | Finding |
| --- | --- |
| ✅ Clean | No emails sent during audit window |

### Models

| Status | Finding |
| --- | --- |
| ✅ Normal | Full model set active — all `retrieved` operations as expected |

### Notifications

| Status | Finding |
| --- | --- |
| ✅ Clean | No notifications sent during audit window |

### Queries

| Status | Finding |
| --- | --- |
| ✅ Normal | All recent queries within acceptable performance (sub-30ms) |
| 🟡 Historical | `character_support_cards` query at **145.84ms** was a one-time cold query |

**Note**: `character_support_cards` table has appropriate indexes (composite on `character_id, position_slot`
and FK on `support_card_id`). The 145ms was a first-run overhead, not a persistent issue.

### Redis

| Status | Finding |
| --- | --- |
| ✅ Healthy | `info` and `ping` commands all sub-1ms |

### Views

| Status | Finding |
| --- | --- |
| ✅ Normal | `admin-layout`, `apm-dashboard`, 404 error view only |

---

## Horizon Tab Audit

### Dashboard

| Status | Finding |
| --- | --- |
| ✅ Healthy | Status: **Active**, Total Processes: 1, Failed Jobs (7 days): **0** |
| ✅ Running | `supervisor-1` on `redis:default` queue |

### Monitoring

| Status | Finding |
| --- | --- |
| ℹ️ Empty | No tags configured — optional feature, nothing to action |

### Metrics — Jobs

| Status | Finding |
| --- | --- |
| ℹ️ Empty | No job history yet since Redis was switched to a live connection |

### Metrics — Queues

| Status | Finding |
| --- | --- |
| ℹ️ Empty | No queue history — accumulates over time after first job completes |

### Horizon Batches

| Status | Finding |
| --- | --- |
| ✅ Clean | No batch jobs |

### Pending / Completed / Silenced Jobs

| Status | Finding |
| --- | --- |
| ✅ Clean | All empty — no backlog; immediate processing working correctly |

### Failed Jobs

| Status | Finding |
| --- | --- |
| ✅ Excellent | **"There aren't any failed jobs"** — zero failures |

---

## Issues Found & Resolutions

### Issue 1: Telescope Exception — jobs_count Undefined (RESOLVED)

+ **Exception**: `Illuminate\View\ViewException: Undefined array key "jobs_count"`
+ **Location**: `resources/views/admin/queue/index.blade.php`
+ **Root Cause**: Stale Blade compiled cache. A previous version of the view used `$jobs_count`
  but the current controller and view do not reference it. The compiled cache contained the old
  version and fired once before auto-regenerating.
+ **Status**: ✅ No action needed — cache was auto-invalidated when view was updated.
  Queue Monitor loads cleanly.

### Issue 2: Telescope Exception — Collection total Method Missing (RESOLVED)

+ **Exception**: `Method Illuminate\Database\Eloquent\Collection::total does not exist`
+ **Location**: `resources/views/support-cards/index.blade.php:185`
+ **Root Cause**: Historical exception from a previous code state. Current
  `SupportCardController::index()` correctly uses `->paginate(24)->withQueryString()` which
  returns a `LengthAwarePaginator` — making `->total()` and `->getCollection()` available.
+ **Status**: ✅ No action needed — current code is correct. Support Cards page loads cleanly.

### Issue 3: Telescope Exception — Undefined Variable races (RESOLVED)

+ **Exception**: `Illuminate\View\ViewException: Undefined variable $races`
+ **Location**: `resources/views/dashboard.blade.php:208`
+ **Root Cause**: Historical exception from a stale compiled view. Current
  `DashboardController::index()` calls `prepareDashboardData()` which always includes `races`
  in both the empty-character case and the normal case.
+ **Status**: ✅ No action needed — current code always passes `$races`. Dashboard loads cleanly.

### Issue 4: Queue Monitor — Horizon Keys in Redis Showing 0 (FIXED)

+ **Location**: `app/Http/Controllers/Admin/QueueController.php`
+ **Root Cause**: `Redis::keys("umamusumecareerplanner_horizon:*")` failed because Laravel's
  PhpRedis client prepends the application prefix (`umamusume-career-planner:`) to the KEYS
  pattern. Horizon writes its keys **without** the application prefix, so the pattern
  `umamusume-career-planner:umamusumecareerplanner_horizon:*` matched nothing.
+ **Fix**: Changed to use `$client->rawCommand('KEYS', "{$horizonPrefix}*")` which bypasses
  the PhpRedis prefix option and queries Redis directly.
+ **Result**: "Horizon Keys in Redis" now shows **6** (correct count).
+ **Status**: ✅ Fixed and tested. All 11 `QueueMonitorTest` tests pass.

---

## Outstanding Items

### Low Priority

| Item | Description | Action |
| --- | --- | --- |
| Keyspace Hit Rate (13–15%) | Caused by `apm:alerts:list` and `query_optimization:stats` always-miss reads when no alerts exist | No action required — this is expected behavior |
| `api.umamusumedb.com` unreachable | External health-check API returns N/A | Investigate if API is still active or remove the health-check call |
| `api.umapyoi.net` 1000–1400ms | External character list API is slow | Cache aggressively; already uses circuit breaker |
| No Scheduled Tasks in Telescope | Schedule tab empty | Verify scheduled tasks are registered correctly in `routes/console.php` |
| Horizon metrics accumulation | Metrics/Jobs and Metrics/Queues tabs empty | Will populate naturally as jobs are processed |
