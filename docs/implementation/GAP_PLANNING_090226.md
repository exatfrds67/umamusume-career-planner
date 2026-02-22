# Plan: Complete Missing & Broken Implementation Overhaul

This plan addresses ~30 stub/placeholder instances across 11 files, 28 failing tests, 63 Larastan errors, 3 "coming
soon" UI gaps, and 4 undocumented-but-required systems (Local Storage Mode, Notifications, Run Snapshots, plus the
missing Notification/Snapshot backends). Work is organized into 8 phases, ordered by dependency and impact. Each phase
should be completed and tested before moving to the next.

Phase 1 — Fix Stub Endpoints & Placeholder Data (Existing Code)

This phase replaces all hardcoded/mock data with real service calls and DB queries. No new models — only wiring existing
services into existing endpoints.

V1 CareerController — Replace 7 stub methods in CareerController.php:

availableRaces() (~L140): Query Race model filtered by career turn/phase instead of returning a single hardcoded race
trainingPredictions() (~L168): Delegate to existing TrainingCalculationService::calculatePredictions() instead of
returning {min:10, max:20, expected:15} for all stats
storeTrainingSession() (~L194): Use existing TrainingService::executeTraining() for calculated stat gains instead of
hardcoded 15
bulkStoreTrainingSessions() (~L230): Same fix as above, iterate with real calculations
storeRace() (~L280): Accept real race data from request instead of hardcoding distance=2000, field_size=18, all
stats=500
patterns() (~L542): Use CareerAnalyticsService for pattern detection instead of static strings
recommendations() (~L572): Use TrainingAdvisoryService or RuleBasedAdvisor instead of hardcoded text
SkillBuildController — Replace all 6 stub methods in SkillBuildController.php:

Create a SkillBuild migration, model, and factory (stores user-saved skill loadouts)
templates(): Query DB for system-defined skill build templates, or generate from Skill model grouped by
category/meta_tier
savedBuilds(): Query SkillBuild model for authenticated user
optimize(): Delegate to SkillOptimizationOrchestrationService for real AI/rule-based optimization
applyBuild(): Create SkillAcquisition records from build's skill list, deduct SP
saveBuild() / deleteBuild(): Standard CRUD on SkillBuild model
SupportCardController (V1) — Fix synergies() in SupportCardController.php:95: Replace hardcoded synergy_score => 0 with
call to SynergyScorer::calculateSynergy()

SkillManagementController — Fix agentPerformance() in SkillManagementController.php:335: Replace 6 hardcoded mock values
with real metrics from MCPToolUsage model queries

APIMonitoringController — Fix historicalMetrics() in APIMonitoringController.php:427: Implement real historical metrics
aggregation from ucp_system_logs or a new metrics table

DashboardController — Fix getRecentResults() in DashboardController.php:514: Query recent Race results and
TrainingSession records instead of returning empty array

Phase 2 — Fix Placeholder Services

CacheManagerService — Fix all 6 warming methods in CacheManagerService.php:565-837:

warmTopCharacters(): Fetch real character data from Character model or UmapyoiApiClient
warmTopSupportCards(): Fetch from SupportCardDefinition model or API
warmRaceDefinitions(): Fetch from Race model's distinct types
warmPopularSkills(): Query Skill model ordered by acquisition count
warmMetaRankings(): Query SupportCardDefinition grouped by meta_tier
warmGameMechanics(): Load from config or seed data
DataFetchingAgent — Fix getPerformanceMetrics() and resetPerformanceMetrics() in DataFetchingAgent.php:841-862: Track
metrics in Redis counters (INCR/GET/DEL) instead of returning zeroes

Context7Service — Fix analyzeContextPatterns() and getContextSummary() in Context7Service.php:236-260: Aggregate real
cache hit/miss data from Redis or ExternalData model queries

WorkflowExportService — Fix exportAsPdf() in WorkflowExportService.php:78-96: Install barryvdh/laravel-dompdf and render
markdown-to-HTML-to-PDF pipeline

Phase 3 — Fix "Coming Soon" UI Gaps

Race Planning in Plan Wizard — Implement Step 4 in create.blade.php:220 and edit.blade.php:207: Build race selection
interface using Race model data, allowing users to pick target races per turn/phase

AI Analysis Drilldown — Replace "coming soon" in predictions.blade.php:159: Build a collapsible detail panel showing AI
reasoning, stat contribution breakdown, and confidence scores from existing TrainingCalculationService data

Notifications Header — Replace "coming soon" in header.blade.php:145: Wire the notification bell to the notification
system (built in Phase 5)

Phase 4 — Local Storage Mode (FR-10)

This is a core architectural requirement per AGENTS.md's Golden Rules.

Create StorageMode enum in Enums with Local and Account values, including helper methods for route prefix resolution

Add UUID-based routes in web.php for local-mode plans/characters (e.g., /local/plans/{uuid}, /local/characters/{uuid})

Create LocalStorageService in Services handling:

UUID generation and validation
localStorage data serialization/deserialization contract
Draft auto-save coordination (30s interval, already partially in local-storage-manager.js)
Conflict detection between local and server data
Create StorageConversionService for local-to-account migration:

Duplicate detection (prevent creating characters that already exist in DB)
Batch conversion with progress tracking
Rollback on failure
Add middleware for storage mode detection — read mode from session/cookie, route accordingly

Update local-storage-manager.js to support the full local-mode contract: offline queue, sync on reconnect, quota
warnings

Add storage mode badge indicator to the app header component

Phase 5 — Notification System (SEQ-008)

Create app/Notifications/ directory with Laravel Notification classes:

TrainingReminderNotification
RaceReadyNotification
CriticalAlertNotification
AchievementUnlockedNotification (placeholder for Phase 7 if added later)
Create notification database migration (notifications table using Laravel's built-in schema)

Create NotificationService in Services with:

Channel routing based on user preferences (from existing notification preference UI in profile)
Priority-based delivery
Quiet hours support
Read/unread status management
Wire the header notification bell (from step 13) to query and display real notifications via an API endpoint

Create queued jobs for notification delivery to avoid blocking request threads

Phase 6 — Run Snapshot & Restore (SEQ-012, high-impact subset)

Create RunSnapshot migration + model with fields: career_id, turn_number, snapshot_data (JSON blob of character stats,
skills, deck, career state), created_at, description

Create RunSnapshotFactory for testing

Create SnapshotService in Services with:

createSnapshot(Career $career, ?string $description) — serialize current state
restoreSnapshot(RunSnapshot $snapshot) — restore career to snapshotted state
compareSnapshots(RunSnapshot $a, RunSnapshot $b) — diff two states
Auto-cleanup (configurable retention, e.g., keep last 20 per career)
Add automatic snapshot triggers: before race execution, at phase transitions (Junior→Classic→Senior→URA), on manual save

Add snapshot management API endpoints and a simple UI panel on the career/character detail view

Phase 7 — Fix Failing Tests & Larastan Errors

Accessibility tests (27 failures): Add missing data-expandable, aria-expanded, aria-labels, aria-controls,
role="region", aria-live="polite" attributes to livewire/advisory-panel.blade.php and related components. Fix WCAG 2.2
AA color contrast violations on hover states, badges, alerts, focus indicators

CriticalAlert API tests (10 failures): Fix SP shortage detection, energy/stamina crisis detection in
CriticalSituationDetector or AdvisoryController. Ensure auth requirements and field validation match test expectations

Dashboard test (1 failure): Fix current_stats string handling in DashboardController — cast to array/object before
accessing properties

Deck Builder tests (4 failures): Fix semantic HTML structure, empty deck slot rendering, card display, auto-slot logic
in deck builder views

External Data Browser tests (3 failures): Fix API response time assertions, concurrent performance, retry button wiring

Recommendation Performance tests (5 failures): Fix rule-based performance to <500ms, cache hit to <50ms, cache
rounding/correctness/invalidation logic

API Endpoint Coverage tests (2 failures): Add missing fallback/recovery and training prediction routes

Larastan errors (63): Fix type safety issues across:

CharacterController.php — undefined property access on models
BatchTrainingPredictionRequest, TrainingPredictionRequest, TrainingRecommendationRequest — null-safe user access
SkillAdviceRequest — proper type casting for iterations
StoreCharacterRequest, UpdateCharacterRequest — explicit string casts
TrainingPredictionResource — proper array offset access
Remove 30 phantom ignore patterns from phpstan.neon
Phase 8 — Code Quality & Missing Form Requests

Extract ~25+ inline validations into dedicated Form Request classes, prioritizing:

PerformanceController (7 inline validations)
SupportCardController (4 inline validations)
AIChatController (3 inline validations)
ProfileController (3 inline validations)
MCPMonitoringController (3 inline validations)
Admin\UserController (2 inline validations)
CharacterController (2 inline validations)
TrainingController (1 inline validation)
Create additional queued jobs for heavy synchronous operations:

ProcessBackupJob — offload backup creation from web request
ProcessDataExportJob — offload large exports
ProcessDataImportJob — offload complex imports
ProcessAIAdvisoryJob — offload AI inference for non-streaming requests
Fix the InvalidateCacheOnGameUpdate listener in Listeners to actually invalidate relevant caches (currently only logs)

Remove misleading "placeholder" comments in DashboardController where code is actually functional

Verification

After each phase, run php artisan test --compact filtered to affected test files
Run vendor/bin/pint --dirty after every file change
Run ./vendor/bin/phpstan analyse after Phase 7 Larastan fixes to confirm zero errors
After Phase 4 (Local Mode), verify UUID routes work end-to-end in both storage modes
After Phase 5, verify notification bell shows real notifications and preferences route correctly
After all phases: php artisan test --compact full suite — target 0 failures
Decisions

Achievement System (SEQ-013) and Inventory Transaction (SEQ-010): Deferred — large systems with no existing code; can be
Phase 9+ in a future iteration
WebSocket/Reverb: Deferred — requires infrastructure setup (Redis, Reverb server); notifications will use polling
initially, with Reverb as a future upgrade
Onboarding Wizard: Deferred — low priority; current welcome page + empty-state messages are functional
MCP integration in API clients: Left as HTTP fallback — MCP fetch server architecture would require significant design
decisions; the HTTP path works correctly
IndexedDB migration: Deferred — localStorage works for current data sizes; IndexedDB is a future scalability improvement
