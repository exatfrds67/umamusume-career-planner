# SPEC-007: External Integration System - Technical Specification

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (External Integration Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (External Integration Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 7.x: External Integration)

**Related Artifacts**:

- PRD: [PRD-007](../prds/PRD-007_External_Integration.md)
- Flow: [FLOW-007](../flows/FLOW-007_External_Integration_System.md)
- Wireframes: [WF-001](../wireframes/WF-001_Dashboard_Overview.md)
- Sequences: [SEQ-007](../sequences/SEQ-007_External_Data_Sync.md), [SEQ-015](../sequences/SEQ-015_Data_Migration_Snapshot_to_Live.md)
- User Flows: [UF-008](../user-flows/UF-008_OCR_and_Data_Import_Flow.md)

## Overview

External Integration manages synchronization with game APIs, community databases, OCR processing, and WebSocket real-time updates.

## Core Features

### 7.1 External API Integration

```php
class ExternalAPIService
{
    const PRIMARY_API = 'https://umapyoi.net/api';
    const FALLBACK_APIS = [
        'https://umamusumedb.com/api',
    ];
    
    public function __construct(
        private CircuitBreaker $circuitBreaker,
        private Cache $cache
    ) {}
    
    public function fetchCharacterData(int $traineeId): array
    {
        $cacheKey = "external:character:{$traineeId}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Try primary API first
        try {
            $data = $this->callAPI(
                self::PRIMARY_API . "/characters/{$traineeId}"
            );
        } catch (APIException $e) {
            // Fallback to secondary APIs
            $data = $this->tryFallbackAPIs($traineeId);
        }
        
        // Cache for 24 hours (game data changes daily)
        $this->cache->put($cacheKey, $data, 86400);
        
        return $data;
    }
    
    private function tryFallbackAPIs(int $traineeId): array
    {
        foreach (self::FALLBACK_APIS as $apiUrl) {
            try {
                return $this->callAPI("{$apiUrl}/characters/{$traineeId}");
            } catch (APIException $e) {
                continue;  // Try next fallback
            }
        }
        
        throw new APIException("All external APIs unavailable");
    }
    
    private function callAPI(string $url): array
    {
        return $this->circuitBreaker->call(
            function () use ($url) {
                $response = Http::timeout(5)->get($url);
                return $response->json();
            }
        );
    }
}
```

### 7.2 OCR Screenshot Processing

```php
class ScreenshotOCRService
{
    public function __construct(
        private TesseractService $tesseract,
        private OpenCVService $opencv
    ) {}
    
    public function extractDataFromScreenshot(string $imagePath): ExtractedData
    {
        // Step 1: Preprocess image (OpenCV)
        $processed = $this->opencv->preprocess($imagePath, [
            'denoise' => true,
            'contrast_enhance' => true,
            'perspective_correct' => true,
        ]);
        
        // Step 2: Run OCR (Tesseract)
        $text = $this->tesseract->ocr($processed, 'jpn+eng');
        
        // Step 3: Parse extracted text
        $data = $this->parseExtractedText($text);
        
        // Step 4: Validate and clean
        return $this->validateAndClean($data);
    }
    
    private function parseExtractedText(string $text): array
    {
        $patterns = [
            'stats' => '/Speed:\s*(\d+)\s*Stamina:\s*(\d+)/i',
            'aptitudes' => '/Mile:\s*([A-Z+]+)\s*Sprint:\s*([A-Z+]+)/i',
            'mood' => '/Mood:\s*(Great|Good|Normal|Bad|Awful)/i',
        ];
        
        $extracted = [];
        
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $extracted[$key] = $matches;
            }
        }
        
        return $extracted;
    }
}
```

### 7.3 WebSocket Real-time Updates

```php
class CharacterUpdateBroadcaster
{
    public function __construct(
        private WebSocketService $websocket
    ) {}
    
    public function broadcastCharacterUpdate(Character $char): void
    {
        $this->websocket->broadcast(
            channel: "character.{$char->id}",
            event: 'character.updated',
            data: [
                'character_id' => $char->id,
                'stats' => $char->stats,
                'mood' => $char->current_mood,
                'energy' => $char->current_energy,
                'timestamp' => now(),
            ]
        );
    }
    
    public function broadcastTrainingSession(TrainingSession $session): void
    {
        $this->websocket->broadcast(
            channel: "character.{$session->character_id}",
            event: 'training.completed',
            data: [
                'session_id' => $session->id,
                'stat_gains' => $session->actual_stat_gains,
                'mood_change' => $session->mood_after,
                'energy_cost' => $session->energy_before - $session->energy_after,
            ]
        );
    }
}
```

### 7.4 Community Tool Integration

```php
class CommunityIntegrationService
{
    const COMMUNITY_TOOLS = [
        'UmamusumeDB' => 'https://umamusumedb.com',
        'UmaPyoi' => 'https://umapyoi.net',
        'Uel' => 'https://uel.ink',
    ];
    
    public function shareCareerResults(Career $career): void
    {
        $shareData = [
            'character_name' => $career->character->name,
            'final_stats' => $career->final_stats,
            'placement' => $career->final_grade,
            'fans' => $career->fans_gained,
            'build' => $career->skill_build,
            'strategy' => $career->race_strategy,
        ];
        
        // Create shareable link
        $shareUrl = $this->createShareLink($shareData);
        
        return $shareUrl;
    }
    
    public function importCommunityTips(Character $char): array
    {
        // Fetch community-contributed tips for this trainee
        $tips = Http::get(
            self::COMMUNITY_TOOLS['UmaPyoi'] . "/tips/{$char->trainee_id}"
        )->json();
        
        return $tips;
    }
}
```

## API Endpoints

### GET /api/v1/external/characters/{traineeId}

Fetch character data from umapyoi.net

### GET /api/v1/external/support-cards

Fetch support card database

### POST /api/v1/screenshot/ocr

Process screenshot and extract data

### POST /api/v1/websocket/subscribe

Subscribe to real-time character updates

### GET /api/v1/community/tips/{traineeId}

Get community tips for character

### POST /api/v1/community/share

Share career results to community

## Database Schema

```sql
CREATE TABLE external_api_cache (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    api_source VARCHAR(100),
    resource_type VARCHAR(50),
    resource_id VARCHAR(100),
    cached_data JSON,
    cached_at TIMESTAMP,
    expires_at TIMESTAMP,
    UNIQUE KEY unique_cache (api_source, resource_type, resource_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB;

CREATE TABLE ocr_extractions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED,
    screenshot_path VARCHAR(255),
    extracted_data JSON,
    confidence_score DECIMAL(5, 2),
    verified_by_user BOOLEAN DEFAULT FALSE,
    extracted_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id),
    INDEX idx_confidence (confidence_score)
) ENGINE=InnoDB;

CREATE TABLE community_shares (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    share_token VARCHAR(255) UNIQUE,
    share_data JSON,
    share_url VARCHAR(255),
    view_count INT DEFAULT 0,
    shared_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id),
    INDEX idx_share_token (share_token)
) ENGINE=InnoDB;
```

## Resilience & Error Handling

### Circuit Breaker Pattern

- Open: API unavailable, use cache only
- Half-Open: Periodically test API recovery
- Closed: API operational, normal operation

### Fallback Strategy

1. Try primary API (umapyoi.net)
2. Try secondary API (UmamusumeDB)
3. Use cached data (24-hour cache)
4. Use local defaults (if all fail)

### Rate Limiting

- 100 requests per minute per endpoint
- Batch requests when possible
- Respect API guidelines

## Testing

- [ ] External API integration with fallbacks
- [ ] OCR accuracy and preprocessing
- [ ] WebSocket real-time updates
- [ ] Community tool data import
- [ ] Circuit breaker behavior
- [ ] Cache invalidation
- [ ] Error recovery and retry logic
- [ ] Rate limit compliance

## Security Considerations

- Never expose API keys in client-side code
- Validate all external data before storage
- Sanitize OCR-extracted text
- Use HTTPS for all external calls
- Implement CORS restrictions

---

**Related**: [PRD-007], [SDS-8], [umapyoi.net API Docs]
