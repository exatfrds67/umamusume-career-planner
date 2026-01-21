# TECH-FLOW-007: External Integration - Technical Flow & Task Breakdown

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (External Integration Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (External Integration Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 7.x: External Integration)

**Related Artifacts**:

- PRD: [PRD-007](../prds/PRD-007_External_Integration.md)
- SPEC: [SPEC-007](../specs/SPEC-007_External_Integration_Technical.md)
- Flow: [FLOW-007](../flows/FLOW-007_External_Integration_System.md)
- Wireframes: [WF-001](../wireframes/WF-001_Dashboard_Overview.md)
- Sequences: [SEQ-007](../sequences/SEQ-007_External_Data_Sync.md), [SEQ-015](../sequences/SEQ-015_Data_Migration_Snapshot_to_Live.md)
- User Flows: [UF-008](../user-flows/UF-008_OCR_and_Data_Import_Flow.md)

## System Architecture

```
┌────────────────────────────────────────────────────────────────┐
│           EXTERNAL INTEGRATION SYSTEM FLOW                     │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  UI Layer (Blade Templates / Vue Components)                 │
│  ├── Dashboard with Real-time Updates                         │
│  ├── OCR Screenshot Upload Interface                          │
│  ├── External Sync Status Panel                               │
│  └── Data Import Wizard                                       │
│           ↓                                                     │
│  API Layer (REST Endpoints)                                   │
│  ├── POST /api/v1/sync/external                               │
│  ├── POST /api/v1/ocr/process                                 │
│  ├── GET /api/v1/sync/status                                  │
│  └── WebSocket: /ws (real-time updates)                       │
│           ↓                                                     │
│  Service Layer (Business Logic)                               │
│  ├── ExternalAPIService                                       │
│  ├── OCRProcessingService (Tesseract)                         │
│  ├── WebSocketBroadcastService (Reverb)                       │
│  └── CommunityDataIntegrationService                          │
│           ↓                                                     │
│  External Services                                            │
│  ├── UmaPyoi.net API (Primary)                                │
│  ├── UmamusumeDB.com API (Fallback)                           │
│  ├── Tesseract OCR Engine                                     │
│  └── Laravel Reverb WebSocket Server                          │
│           ↓                                                     │
│  Database Layer (MySQL)                                       │
│  ├── external_sync_logs table                                 │
│  ├── ocr_uploads table                                        │
│  └── sync_conflicts table                                     │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagrams

### 7.1 External API Sync Flow

```
User triggers external sync
    ↓
ExternalSyncController::sync()
    ↓
ExternalAPIService::syncCharacterData()
    │
    ├─→ Try Primary API (UmaPyoi.net)
    │   ├─→ GET /api/v1/characters/{external_id}
    │   ├─→ If 200 OK → Parse response
    │   └─→ If error → Try fallback
    │
    ├─→ Try Fallback API (UmamusumeDB.com)
    │   ├─→ GET /api/characters/{external_id}
    │   └─→ Return data or error
    │
    ├─→ Compare external data vs local data
    │   ├─→ Detect conflicts (stat differences)
    │   ├─→ Log conflicts to sync_conflicts table
    │   └─→ Determine resolution strategy
    │
    ├─→ Merge strategy selection
    │   ├─→ "Local Wins": Keep local data
    │   ├─→ "External Wins": Overwrite with external
    │   ├─→ "Manual Review": Flag for user decision
    │   └─→ Apply selected strategy
    │
    ├─→ Update character state
    ├─→ Log sync to external_sync_logs
    └─→ Broadcast WebSocket event
            ↓
Response with sync summary
```

### 7.2 OCR Processing Flow

```
User uploads screenshot (training results, character stats, etc.)
    ↓
OCRController::process()
    ↓
OCRProcessingService::extractData()
    │
    ├─→ Validate image format (PNG, JPG)
    ├─→ Save to storage/ocr_uploads
    ├─→ Invoke Tesseract OCR
    │   ├─→ tesseract image.png output -l jpn+eng
    │   ├─→ Extract text regions
    │   ├─→ Parse structured data (stats, skills, etc.)
    │   └─→ Return OCRResult
    │
    ├─→ Apply OCR corrections
    │   ├─→ Common misreads: "スピード" detection
    │   ├─→ Stat value validation (0-1200 range)
    │   └─→ Character name matching
    │
    ├─→ Create OCRUpload record
    ├─→ Auto-apply data if confidence > 90%
    ├─→ If confidence < 90% → Flag for manual review
    └─→ Return structured data
            ↓
Response with extracted data + confidence scores
```

### 7.3 WebSocket Real-time Update Flow

```
Server-side event (training complete, race finish, sync update)
    ↓
Event Listener (CharacterStateChanged, RaceCompleted, etc.)
    ↓
WebSocketBroadcastService::broadcast()
    │
    ├─→ Format event payload
    ├─→ Broadcast to Laravel Reverb
    │   ├─→ Channel: character.{character_id}
    │   ├─→ Event: CharacterUpdated
    │   └─→ Payload: { character_id, updated_fields, timestamp }
    │
    └─→ Reverb broadcasts to connected clients
            ↓
Client receives WebSocket message
    ↓
Vue component updates UI reactively
```

## Implementation Tasks

### Task 7.1: External API Integration (Week 1-2, ~20 hours)

- [ ] **7.1.1**: Create ExternalAPIService
  - HTTP client for UmaPyoi.net API
  - Fallback logic to UmamusumeDB.com
  - Response parsing and normalization
  - Rate limiting (10 requests/minute)
  - Unit tests: 8 tests
  - **Files**: `app/Services/ExternalAPIService.php`
  - **Effort**: 10 hours

- [ ] **7.1.2**: Create SyncConflictResolver
  - Detect data conflicts (local vs external)
  - Resolution strategies (Local Wins, External Wins, Manual)
  - Conflict logging
  - Unit tests: 6 tests
  - **Files**: `app/Services/SyncConflictResolver.php`
  - **Effort**: 8 hours

- [ ] **7.1.3**: Create external API adapters
  - UmaPyoiAdapter (primary)
  - UmamusumeDBAdapter (fallback)
  - Common interface for both
  - Unit tests: 4 tests
  - **Files**: `app/Services/Adapters/UmaPyoiAdapter.php`, `UmamusumeDBAdapter.php`
  - **Effort**: 2 hours

### Task 7.2: OCR Processing (Week 2, ~18 hours)

- [ ] **7.2.1**: Install and configure Tesseract
  - Install Tesseract OCR engine
  - Download Japanese language pack
  - Test OCR accuracy on sample screenshots
  - **Effort**: 4 hours

- [ ] **7.2.2**: Create OCRProcessingService
  - Invoke Tesseract CLI from PHP
  - Parse OCR output text
  - Extract structured data (stats, skills, etc.)
  - Apply OCR corrections for common errors
  - Unit tests: 8 tests
  - **Files**: `app/Services/OCRProcessingService.php`
  - **Effort**: 10 hours

- [ ] **7.2.3**: Create OCRDataValidator
  - Validate extracted stat values (0-1200 range)
  - Character name fuzzy matching
  - Confidence scoring (0-100%)
  - Unit tests: 4 tests
  - **Files**: `app/Services/OCRDataValidator.php`
  - **Effort**: 4 hours

### Task 7.3: WebSocket Real-time Updates (Week 3, ~16 hours)

- [ ] **7.3.1**: Install and configure Laravel Reverb
  - Install Laravel Reverb package
  - Configure WebSocket server
  - Set up SSL for secure WebSocket (wss://)
  - **Effort**: 4 hours

- [ ] **7.3.2**: Create WebSocketBroadcastService
  - Broadcast events to Reverb
  - Channel management (character.{id}, training.{id}, etc.)
  - Event formatting
  - Unit tests: 5 tests
  - **Files**: `app/Services/WebSocketBroadcastService.php`
  - **Effort**: 6 hours

- [ ] **7.3.3**: Create event listeners
  - CharacterStateChanged → broadcast update
  - RaceCompleted → broadcast results
  - TrainingSessionCompleted → broadcast outcome
  - SkillAcquired → broadcast notification
  - **Files**: `app/Listeners/BroadcastCharacterUpdate.php`, etc.
  - **Effort**: 4 hours

- [ ] **7.3.4**: Implement Vue WebSocket client
  - Connect to Reverb from frontend
  - Subscribe to character channels
  - Update UI reactively on message receipt
  - **Files**: `resources/js/services/WebSocketService.js`
  - **Effort**: 2 hours

### Task 7.4: Database & Models (Week 3, ~6 hours)

- [ ] **7.4.1**: Create external_sync_logs migration
  - Fields: character_id, api_provider, sync_type, conflicts_detected, status, synced_at
  - **Files**: `database/migrations/*_create_external_sync_logs_table.php`
  - **Effort**: 2 hours

- [ ] **7.4.2**: Create ocr_uploads migration
  - Fields: character_id, image_path, extracted_data_json, confidence_score, status, uploaded_at
  - **Files**: `database/migrations/*_create_ocr_uploads_table.php`
  - **Effort**: 2 hours

- [ ] **7.4.3**: Create sync_conflicts migration
  - Fields: character_id, field_name, local_value, external_value, resolution_strategy, resolved_at
  - **Files**: `database/migrations/*_create_sync_conflicts_table.php`
  - **Effort**: 2 hours

### Task 7.5: API Layer (Week 4, ~12 hours)

- [ ] **7.5.1**: Create ExternalSyncController
  - POST /api/v1/sync/external (trigger sync)
  - GET /api/v1/sync/status (sync history)
  - GET /api/v1/sync/conflicts (unresolved conflicts)
  - POST /api/v1/sync/conflicts/{id}/resolve (manual resolution)
  - **Files**: `app/Http/Controllers/API/ExternalSyncController.php`
  - **Effort**: 6 hours

- [ ] **7.5.2**: Create OCRController
  - POST /api/v1/ocr/process (upload & process screenshot)
  - GET /api/v1/ocr/history (OCR upload log)
  - POST /api/v1/ocr/{id}/apply (apply extracted data)
  - **Files**: `app/Http/Controllers/API/OCRController.php`
  - **Effort**: 6 hours

### Task 7.6: Testing & Integration (Week 4, ~12 hours)

- [ ] **7.6.1**: Feature tests
  - Complete external sync flow
  - OCR processing with sample images
  - Conflict resolution workflows
  - WebSocket event broadcasting
  - Feature tests: 10 tests
  - **Files**: `tests/Feature/ExternalIntegrationTest.php`
  - **Effort**: 8 hours

- [ ] **7.6.2**: Mock external APIs
  - Mock UmaPyoi.net responses
  - Mock UmamusumeDB.com responses
  - Simulate API failures for fallback testing
  - Unit tests: 6 tests
  - **Effort**: 4 hours

## Summary

**Total Effort**: ~84 hours (4 weeks)

**Total Tests**: 25+ (18 unit tests, 10 feature tests)

**Key Deliverables**:

- External API integration with fallback (UmaPyoi.net → UmamusumeDB.com)
- OCR processing with Tesseract (Japanese + English)
- Real-time WebSocket updates via Laravel Reverb
- Conflict resolution system for data synchronization
- 7 REST endpoints for external integration and OCR
- 3 database tables for sync logs, OCR uploads, conflicts
- Vue WebSocket client for real-time UI updates

**Dependencies**:

- Requires Tesseract OCR installed on server
- Requires Laravel Reverb for WebSocket functionality
- Integrates with all other modules (SPEC-001 through SPEC-006) for data sync
- External API availability (UmaPyoi.net, UmamusumeDB.com)

**Infrastructure Requirements**:

- Tesseract OCR engine with Japanese language pack
- SSL certificate for secure WebSocket (wss://)
- Laravel Reverb server running
- Sufficient storage for OCR screenshot uploads
