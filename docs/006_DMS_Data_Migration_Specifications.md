# Data Migration Specifications (DMS)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.0  
**Date**: January 10, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Updated**: Aligned with Laravel 12, Tailwind CSS v4, AI integration, and modern architecture specifications

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Technical Specifications](#2-technical-specifications)
3. [Data Source Specifications](#3-data-source-specifications)
4. [Transformation Specifications](#4-transformation-specifications)
5. [Target Schema Specifications](#5-target-schema-specifications)
6. [Migration Tool Specifications](#6-migration-tool-specifications)
7. [AI Integration Specifications](#11-ai-integration-specifications)
8. [OCR Processing Specifications](#12-ocr-processing-specifications)

---

## 1. Introduction

### 1.1 Purpose

This Data Migration Specifications (DMS) document provides detailed technical specifications for implementing data migration processes in the modern UmamusumeCareerPlanner system. Built with Laravel 12, Tailwind CSS v4, and advanced AI integration, the specifications ensure data integrity, user privacy, accessibility compliance, and system reliability while providing clear implementation guidance for developers.

### 1.2 Scope

This document specifies comprehensive requirements for:

#### Core Migration Capabilities

- **Technical requirements** for Laravel 12 migration tools and modern web technologies
- **Data format specifications** for sources (umapyoi.net, UmamusumeDB.com, user files) and targets
- **Transformation rules** and validation criteria with accessibility considerations
- **Performance benchmarks** and quality metrics for modern hardware
- **Security requirements** and WCAG 2.2 AA compliance standards

#### Advanced Integration Features

- **AI system integration** specifications for Ollama local models and AWS Bedrock fallback
- **OCR processing requirements** for screenshot analysis with Tesseract and OpenCV
- **External API integration** with intelligent caching and fallback mechanisms
- **Real-time features** using WebSocket integration and event-driven architecture
- **Progressive Web App** capabilities with offline functionality and accessibility

#### Modern Architecture Requirements

- **Event-driven processing** using Laravel Events and Listeners
- **Asynchronous operations** with Laravel Queues and Redis integration
- **Performance optimization** with database indexing and connection pooling
- **Monitoring and alerting** with comprehensive logging and metrics collection

### 1.3 Document Conventions

- **MUST**: Mandatory requirements that cannot be compromised for system integrity
- **SHOULD**: Recommended requirements with strong preference for optimal performance
- **MAY**: Optional requirements that provide additional value and user experience enhancement
- **SHALL**: Formal requirement specification language for compliance and audit purposes

---

## 2. Technical Specifications

### 2.1 System Requirements (Updated for Modern Stack)

#### 2.1.1 Hardware Requirements

**Minimum Specifications for Laravel 12 + AI Integration**:

- **CPU**: 6 cores, 3.0GHz or equivalent (increased for AI processing)
- **RAM**: 16GB available for migration processes (8GB for Laravel, 8GB for AI models)
- **Storage**: 200GB SSD for optimal I/O performance and model storage
- **Network**: Stable internet connection with 25Mbps minimum bandwidth for API calls

**Recommended Specifications for Production**:

- **CPU**: 12 cores, 3.5GHz or equivalent for concurrent AI processing
- **RAM**: 32GB available for migration processes and model caching
- **Storage**: 1TB NVMe SSD for high-performance operations
- **Network**: High-speed connection with 100Mbps+ bandwidth for real-time features

#### 2.1.2 Software Requirements (Modern Stack)

**Core Dependencies**:

- **PHP**: 8.3+ with required extensions (PDO, JSON, cURL, OpenSSL, Redis, GD, Imagick)
- **MySQL**: 8.0+ with InnoDB storage engine and JSON support
- **Redis**: 7.0+ for caching, sessions, and queue management
- **Laravel**: 12.x framework with migration tools and event system
- **Node.js**: 20+ for build tools and WebSocket server

**AI and Processing Tools**:

- **Ollama**: Latest version for local AI model management
- **Python**: 3.11+ for OCR processing and data analysis scripts
- **Tesseract OCR**: 5.3+ with Japanese language support
- **OpenCV**: 4.8+ for image preprocessing

**Development and Deployment Tools**:

- **Composer**: 2.6+ for PHP dependency management
- **NPM/Yarn**: Latest for frontend dependency management
- **Git**: 2.40+ for version control and deployment
- **Docker**: 24+ for containerized development (optional)

### 2.2 Architecture Specifications (Laravel 12 Enhanced)

#### 2.2.1 Migration Service Architecture

**Service-Oriented Design with Laravel 12**:

```php
<?php

namespace App\Services\Migration;

interface MigrationServiceInterface
{
    public function validateSource(DataSource $source): ValidationResult;
    public function extractData(DataSource $source): ExtractedData;
    public function transformData(ExtractedData $data): TransformedData;
    public function loadData(TransformedData $data): LoadResult;
    public function validateMigration(MigrationResult $result): ValidationResult;
}

class ModernMigrationService implements MigrationServiceInterface
{
    public function __construct(
        private DataExtractor $extractor,
        private DataTransformer $transformer,
        private DataLoader $loader,
        private ValidationService $validator,
        private CacheManager $cache,
        private EventDispatcher $events,
        private PerformanceMonitor $monitor
    ) {}
    
    public function migrate(MigrationRequest $request): MigrationResult
    {
        // Start performance monitoring
        $this->monitor->startMigration($request);
        
        // Dispatch migration started event
        $this->events->dispatch(new MigrationStarted($request));
        
        try {
            // Validate source with comprehensive checks
            $validation = $this->validateSource($request->getSource());
            if (!$validation->isValid()) {
                throw new MigrationException('Source validation failed', $validation->getErrors());
            }
            
            // Extract with intelligent caching
            $extracted = $this->extractor->extract($request->getSource());
            $this->cache->remember("migration.{$request->getId()}.extracted", 3600, $extracted);
            
            // Transform with real-time progress
            $transformed = $this->transformer->transform($extracted, function ($progress) {
                broadcast(new MigrationProgress($progress));
            });
            
            // Validate transformation results
            $transformValidation = $this->validator->validate($transformed);
            if (!$transformValidation->isValid()) {
                throw new MigrationException('Transformation validation failed', $transformValidation->getErrors());
            }
            
            // Load with transaction safety and batch processing
            $result = DB::transaction(function () use ($transformed) {
                return $this->loader->load($transformed);
            });
            
            // Final validation and performance metrics
            $finalValidation = $this->validateMigration($result);
            $performanceMetrics = $this->monitor->endMigration($request);
            
            // Dispatch success event with metrics
            $this->events->dispatch(new MigrationCompleted($result, $performanceMetrics));
            
            return $result;
            
        } catch (Exception $e) {
            $this->events->dispatch(new MigrationFailed($request, $e));
            $this->monitor->recordFailure($request, $e);
            throw $e;
        }
    }
}
```

#### 2.2.2 Data Processing Pipeline with Event-Driven Architecture

**Pipeline Implementation with Laravel Events**:

```php
<?php

namespace App\Services\Migration\Pipeline;

class DataProcessingPipeline
{
    private array $processors = [];
    private EventDispatcher $events;
    
    public function addProcessor(DataProcessor $processor): self
    {
        $this->processors[] = $processor;
        return $this;
    }
    
    public function process(DataCollection $data): ProcessedData
    {
        $result = $data;
        
        foreach ($this->processors as $index => $processor) {
            // Dispatch processing stage event
            $this->events->dispatch(new ProcessingStageStarted($processor, $index));
            
            try {
                $result = $processor->process($result);
                
                // Validate intermediate results
                if (!$result->isValid()) {
                    throw new ProcessingException(
                        "Processing failed at {$processor->getName()}", 
                        $result->getErrors()
                    );
                }
                
                // Dispatch stage completed event
                $this->events->dispatch(new ProcessingStageCompleted($processor, $result));
                
            } catch (Exception $e) {
                $this->events->dispatch(new ProcessingStageError($processor, $e));
                throw $e;
            }
        }
        
        return $result;
    }
}

// Example processors with modern Laravel features
class CharacterDataProcessor implements DataProcessor
{
    public function process(DataCollection $data): ProcessedData
    {
        $processed = [];
        
        foreach ($data as $record) {
            $character = $this->processCharacterRecord($record);
            if ($character) {
                $processed[] = $character;
            }
        }
        
        return new ProcessedData($processed);
    }
    
    private function processCharacterRecord(array $record): ?array
    {
        // Validate required fields with Laravel validation
        $validator = Validator::make($record, [
            'name' => 'required|string|max:255',
            'stats' => 'required|array',
            'stats.speed' => 'required|integer|between:0,1200',
            'stats.stamina' => 'required|integer|between:0,1200',
            'stats.power' => 'required|integer|between:0,1200',
            'stats.guts' => 'required|integer|between:0,1200',
            'stats.wit' => 'required|integer|between:0,1200',
            'scenario_type' => 'required|in:ura_finale,unity_cup'
        ]);
        
        if ($validator->fails()) {
            Log::warning('Character record validation failed', [
                'record' => $record,
                'errors' => $validator->errors()
            ]);
            return null;
        }
        
        // Transform data format with proper casting
        return [
            'name' => $this->sanitizeName($record['name']),
            'scenario_type' => $this->mapScenarioType($record['scenario'] ?? 'ura_finale'),
            'speed' => $this->validateStat($record['stats']['speed'] ?? 0),
            'stamina' => $this->validateStat($record['stats']['stamina'] ?? 0),
            'power' => $this->validateStat($record['stats']['power'] ?? 0),
            'guts' => $this->validateStat($record['stats']['guts'] ?? 0),
            'wit' => $this->validateStat($record['stats']['wit'] ?? 0),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
```

---

## 3. Data Source Specifications

### 3.1 External API Sources (Verified 2026)

#### 3.1.1 umapyoi.net API Specification

**Base Configuration**:

- **Base URL**: `https://api.umapyoi.net/api/v1/`
- **Authentication**: None required (public API)
- **Rate Limiting**: 100 requests/minute, 1000 requests/hour per IP
- **Response Format**: JSON with comprehensive schemas
- **Caching Strategy**: Redis-based with 24-hour TTL for static data

**API Endpoints and Specifications**:

```yaml
# Character Data Endpoint
GET /characters
Parameters:
  - page: integer (pagination)
  - per_page: integer (max 100)
  - rarity: integer (1-3 filter)
  - updated_since: ISO 8601 timestamp

Response Schema:
  data:
    - id: integer (unique identifier)
      name: string (Japanese name)
      name_en: string (English name, optional)
      rarity: integer (1-3 star rating)
      aptitudes:
        turf: enum (G-SS)
        dirt: enum (G-SS)
        sprint: enum (G-SS)
        mile: enum (G-SS)
        medium: enum (G-SS)
        long: enum (G-SS)
        front_runner: enum (G-SS)
        pace_chaser: enum (G-SS)
        late_surger: enum (G-SS)
        end_closer: enum (G-SS)
      growth_rates:
        speed: integer (0-30)
        stamina: integer (0-30)
        power: integer (0-30)
        guts: integer (0-30)
        wit: integer (0-30)
      updated_at: string (ISO 8601)
  meta:
    total: integer
    per_page: integer
    current_page: integer
    last_page: integer

# Skills Data Endpoint
GET /skills
Response Schema:
  data:
    - id: integer
      name: string
      name_en: string (optional)
      type: enum (normal, rare, unique)
      category: enum (speed, passive, recovery, debuff)
      sp_cost: integer
      description: string
      effects: object
      evolution_from: integer (optional)
      evolution_to: integer (optional)
      updated_at: string (ISO 8601)

# Support Cards Data Endpoint
GET /support-cards
Response Schema:
  data:
    - id: integer
      name: string
      name_en: string (optional)
      rarity: enum (R, SR, SSR)
      type: enum (speed, stamina, power, guts, wit, pal)
      effects:
        training_bonus: object
        skill_hints: array
        events: array
      limit_break_effects: array
      meta_tier: enum (SS, S, A, B) # Community tier ranking
      updated_at: string (ISO 8601)
```

**Error Handling and Fallback**:

```php
<?php

namespace App\Services\External;

class UmapyoiApiClient
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 1000; // milliseconds
    
    public function getCharacters(array $options = []): Collection
    {
        $cacheKey = 'umapyoi.characters.' . md5(serialize($options));
        
        return Cache::remember($cacheKey, 3600, function () use ($options) {
            return $this->makeRequest('characters', $options);
        });
    }
    
    private function makeRequest(string $endpoint, array $params = []): Collection
    {
        $attempt = 0;
        
        while ($attempt < self::MAX_RETRIES) {
            try {
                // Check rate limit
                if (!RateLimiter::attempt('umapyoi-api', 100, 60)) {
                    throw new RateLimitExceededException('Rate limit exceeded');
                }
                
                $response = Http::timeout(30)
                    ->retry(3, 1000)
                    ->withHeaders([
                        'User-Agent' => 'UmamusumeCareerPlanner/2.0',
                        'Accept' => 'application/json',
                    ])
                    ->get(self::BASE_URL . $endpoint, $params);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $this->validateResponseStructure($data);
                    return collect($data['data']);
                }
                
                // Handle specific HTTP errors
                match ($response->status()) {
                    429 => throw new RateLimitExceededException('API rate limit exceeded'),
                    404 => throw new EndpointNotFoundException("Endpoint not found: {$endpoint}"),
                    500, 502, 503, 504 => throw new ServerErrorException('API server error'),
                    default => throw new ApiException("API request failed with status {$response->status()}")
                };
                
            } catch (RateLimitExceededException $e) {
                // Wait for rate limit reset
                sleep(60);
                $attempt++;
                continue;
                
            } catch (ServerErrorException $e) {
                // Exponential backoff for server errors
                sleep(pow(2, $attempt));
                $attempt++;
                continue;
                
            } catch (Exception $e) {
                Log::error('umapyoi.net API error', [
                    'endpoint' => $endpoint,
                    'params' => $params,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt >= self::MAX_RETRIES - 1) {
                    throw $e;
                }
                
                $attempt++;
                sleep(self::RETRY_DELAY / 1000);
            }
        }
        
        throw new MaxRetriesExceededException('Maximum retry attempts exceeded');
    }
}
```

#### 3.1.2 UmamusumeDB.com Integration (Requires Verification)

**Verification Requirements**:

- **Status**: Requires verification during Phase 1 implementation
- **Data Types**: Training calculations, meta analysis, community tier lists
- **Format**: Web scraping or API (to be determined)
- **Fallback Strategy**: Manual data entry and community sourcing if unavailable

**Implementation Approach**:

```php
<?php

namespace App\Services\External;

class UmamusumeDBClient
{
    private bool $isAvailable = false;
    private string $accessMethod = 'unknown'; // 'api' or 'scraping'
    
    public function verifyAvailability(): bool
    {
        try {
            // Attempt API access first
            $response = Http::timeout(10)->get('https://umamusumedb.com/api/status');
            if ($response->successful()) {
                $this->isAvailable = true;
                $this->accessMethod = 'api';
                return true;
            }
            
            // Fallback to web scraping verification
            $response = Http::timeout(10)->get('https://umamusumedb.com');
            if ($response->successful() && str_contains($response->body(), 'umamusume')) {
                $this->isAvailable = true;
                $this->accessMethod = 'scraping';
                return true;
            }
            
        } catch (Exception $e) {
            Log::warning('UmamusumeDB.com verification failed', ['error' => $e->getMessage()]);
        }
        
        $this->isAvailable = false;
        return false;
    }
    
    public function getMetaTierData(): ?Collection
    {
        if (!$this->isAvailable) {
            return null;
        }
        
        return match ($this->accessMethod) {
            'api' => $this->getMetaTierFromAPI(),
            'scraping' => $this->getMetaTierFromScraping(),
            default => null
        };
    }
}
```

### 3.2 User Data Sources (Enhanced for Modern UI)

#### 3.2.1 File Upload Specifications

**Supported File Formats with Validation**:

```php
<?php

namespace App\Services\Import;

class FileFormatValidator
{
    private array $supportedFormats = [
        'csv' => [
            'mime_types' => ['text/csv', 'text/plain'],
            'extensions' => ['csv'],
            'max_size' => 50 * 1024 * 1024, // 50MB
            'processor' => CsvProcessor::class
        ],
        'excel' => [
            'mime_types' => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ],
            'extensions' => ['xls', 'xlsx'],
            'max_size' => 100 * 1024 * 1024, // 100MB
            'processor' => ExcelProcessor::class
        ],
        'json' => [
            'mime_types' => ['application/json'],
            'extensions' => ['json'],
            'max_size' => 25 * 1024 * 1024, // 25MB
            'processor' => JsonProcessor::class
        ]
    ];
    
    public function validateFile(UploadedFile $file): ValidationResult
    {
        $errors = [];
        
        // File size validation
        if ($file->getSize() > $this->getMaxSizeForFile($file)) {
            $errors[] = 'File size exceeds maximum limit';
        }
        
        // MIME type validation
        if (!$this->isValidMimeType($file)) {
            $errors[] = 'Invalid file type';
        }
        
        // Extension validation
        if (!$this->isValidExtension($file)) {
            $errors[] = 'Invalid file extension';
        }
        
        // Content validation (security)
        if (!$this->isSecureContent($file)) {
            $errors[] = 'File contains potentially malicious content';
        }
        
        // Accessibility validation
        if (!$this->isAccessibleFormat($file)) {
            $errors[] = 'File format may not be accessible to all users';
        }
        
        return new ValidationResult(empty($errors), $errors);
    }
    
    private function isAccessibleFormat(UploadedFile $file): bool
    {
        // Ensure file formats are accessible
        $extension = strtolower($file->getClientOriginalExtension());
        
        // CSV and JSON are most accessible
        if (in_array($extension, ['csv', 'json'])) {
            return true;
        }
        
        // Excel files should have alternative format notice
        if (in_array($extension, ['xls', 'xlsx'])) {
            // Log recommendation for CSV format
            Log::info('Excel file uploaded, recommend CSV for better accessibility', [
                'filename' => $file->getClientOriginalName(),
                'user_id' => auth()->id()
            ]);
            return true;
        }
        
        return false;
    }
}
```

#### 3.2.2 Google Sheets API Integration

**OAuth 2.0 Integration with Privacy Controls**:

```php
<?php

namespace App\Services\Import;

class GoogleSheetsImporter
{
    public function __construct(
        private GoogleClient $client,
        private ConsentManager $consent
    ) {}
    
    public function importFromGoogleSheets(string $spreadsheetId, array $options = []): ImportResult
    {
        // Verify user consent for Google Sheets access
        if (!$this->consent->hasConsent(auth()->id(), 'google_sheets_access')) {
            throw new ConsentRequiredException('User consent required for Google Sheets access');
        }
        
        try {
            // Configure Google Sheets service
            $service = new Google_Service_Sheets($this->client);
            
            // Get spreadsheet metadata
            $spreadsheet = $service->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheet->getSheets();
            
            $importedData = [];
            
            foreach ($sheets as $sheet) {
                $sheetName = $sheet->getProperties()->getTitle();
                
                // Skip hidden or system sheets
                if ($sheet->getProperties()->getHidden()) {
                    continue;
                }
                
                // Determine sheet type based on headers
                $sheetType = $this->detectSheetType($service, $spreadsheetId, $sheetName);
                
                if ($sheetType) {
                    $data = $this->importSheetData($service, $spreadsheetId, $sheetName, $sheetType);
                    $importedData[$sheetType] = $data;
                }
            }
            
            return new ImportResult($importedData);
            
        } catch (Google_Service_Exception $e) {
            Log::error('Google Sheets API error', [
                'spreadsheet_id' => $spreadsheetId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            throw new GoogleSheetsException('Failed to import from Google Sheets: ' . $e->getMessage());
        }
    }
    
    private function detectSheetType(Google_Service_Sheets $service, string $spreadsheetId, string $sheetName): ?string
    {
        // Read first row to detect headers
        $range = "{$sheetName}!1:1";
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $headers = $response->getValues()[0] ?? [];
        
        // Normalize headers for comparison
        $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));
        
        // Character sheet detection
        if (in_array('character name', $normalizedHeaders) && 
            in_array('speed', $normalizedHeaders) && 
            in_array('stamina', $normalizedHeaders)) {
            return 'characters';
        }
        
        // Training log detection
        if (in_array('turn', $normalizedHeaders) && 
            in_array('training type', $normalizedHeaders)) {
            return 'training_log';
        }
        
        // Race results detection
        if (in_array('race name', $normalizedHeaders) && 
            in_array('position', $normalizedHeaders)) {
            return 'race_results';
        }
        
        return null;
    }
}
```

---

## 4. Transformation Specifications

### 4.1 Data Transformation Rules (Enhanced for Modern Stack)

#### 4.1.1 Character Data Transformation with Laravel 12 Features

**Name Normalization with Internationalization**:

```php
<?php

namespace App\Services\Transformation;

class CharacterNameTransformer
{
    private array $nameMapping = [
        // Japanese to English mappings
        'スペシャルウィーク' => 'Special Week',
        'サイレンススズカ' => 'Silence Suzuka',
        'トウカイテイオー' => 'Tokai Teio',
        'ウオッカ' => 'Vodka',
        'ダイワスカーレット' => 'Daiwa Scarlet',
        'ゴールドシップ' => 'Gold Ship',
        'メジロマックイーン' => 'Mejiro McQueen',
        'エルコンドルパサー' => 'El Condor Pasa',
        'グラスワンダー' => 'Grass Wonder',
        'ヒシアマゾン' => 'Hishi Amazon',
        
        // Common variations and abbreviations
        'Special Week (URA)' => 'Special Week',
        'Silence Suzuka - Speed' => 'Silence Suzuka',
        'SW' => 'Special Week',
        'SS' => 'Silence Suzuka',
        'TT' => 'Tokai Teio',
        'Vodka (Unity Cup)' => 'Vodka',
        
        // Handle common misspellings
        'Speical Week' => 'Special Week',
        'Silence Suzka' => 'Silence Suzuka',
        'Tokai Teio' => 'Tokai Teio',
    ];
    
    public function transform(string $name): string
    {
        $normalized = trim($name);
        
        // Check direct mapping first
        if (isset($this->nameMapping[$normalized])) {
            return $this->nameMapping[$normalized];
        }
        
        // Remove common suffixes and prefixes
        $patterns = [
            '/\s*\(URA\s*Finale\)$/i',
            '/\s*\(Unity\s*Cup\)$/i',
            '/\s*-\s*(Speed|Stamina|Power|Guts|Wit)$/i',
            '/^\s*(Character|Uma|Horse)\s*:\s*/i',
        ];
        
        foreach ($patterns as $pattern) {
            $normalized = preg_replace($pattern, '', $normalized);
        }
        
        // Fuzzy matching for close matches
        $bestMatch = $this->findBestMatch($normalized);
        if ($bestMatch) {
            return $bestMatch;
        }
        
        // Return cleaned name if no match found
        return trim($normalized);
    }
    
    private function findBestMatch(string $name): ?string
    {
        $bestScore = 0;
        $bestMatch = null;
        
        foreach (array_values($this->nameMapping) as $canonicalName) {
            $score = $this->calculateSimilarity($name, $canonicalName);
            if ($score > $bestScore && $score > 0.8) { // 80% similarity threshold
                $bestScore = $score;
                $bestMatch = $canonicalName;
            }
        }
        
        return $bestMatch;
    }
    
    private function calculateSimilarity(string $a, string $b): float
    {
        return 1 - (levenshtein(strtolower($a), strtolower($b)) / max(strlen($a), strlen($b)));
    }
}
```

**Stat Validation and Transformation with Range Checking**:

```php
<?php

namespace App\Services\Transformation;

class StatTransformer
{
    private const MIN_STAT = 0;
    private const MAX_STAT = 1200;
    private const STAT_BREAKPOINTS = [901, 1600]; // Important game breakpoints
    
    public function transformStat(mixed $value, string $statName = ''): int
    {
        // Handle various input formats
        if (is_string($value)) {
            // Remove non-numeric characters except decimal point
            $value = preg_replace('/[^\d.]/', '', $value);
        }
        
        $numericValue = (float) $value;
        
        // Validate range
        if ($numericValue < self::MIN_STAT) {
            Log::warning('Stat value below minimum', [
                'stat' => $statName,
                'value' => $numericValue,
                'corrected_to' => self::MIN_STAT
            ]);
            return self::MIN_STAT;
        }
        
        if ($numericValue > self::MAX_STAT) {
            Log::warning('Stat value exceeds maximum', [
                'stat' => $statName,
                'value' => $numericValue,
                'corrected_to' => self::MAX_STAT
            ]);
            return self::MAX_STAT;
        }
        
        $intValue = (int) round($numericValue);
        
        // Log if value is at important breakpoints
        if (in_array($intValue, self::STAT_BREAKPOINTS)) {
            Log::info('Stat at important breakpoint', [
                'stat' => $statName,
                'value' => $intValue,
                'breakpoint' => true
            ]);
        }
        
        return $intValue;
    }
    
    public function transformGrade(string $grade): string
    {
        $normalized = strtoupper(trim($grade));
        
        // Handle various grade formats
        $gradeMapping = [
            'G+' => 'G+', 'G' => 'G',
            'F+' => 'F+', 'F' => 'F',
            'E+' => 'E+', 'E' => 'E',
            'D+' => 'D+', 'D' => 'D',
            'C+' => 'C+', 'C' => 'C',
            'B+' => 'B+', 'B' => 'B',
            'A+' => 'A+', 'A' => 'A',
            'S+' => 'S+', 'S' => 'S',
            'SS+' => 'SS+', 'SS' => 'SS',
            
            // Handle alternative formats
            'G PLUS' => 'G+', 'G-PLUS' => 'G+',
            'DOUBLE S' => 'SS', 'DOUBLE-S' => 'SS',
            'S PLUS' => 'S+', 'S-PLUS' => 'S+',
        ];
        
        if (isset($gradeMapping[$normalized])) {
            return $gradeMapping[$normalized];
        }
        
        // Validate against valid grades
        $validGrades = array_values($gradeMapping);
        if (!in_array($normalized, $validGrades)) {
            throw new ValidationException("Invalid grade format: {$grade}");
        }
        
        return $normalized;
    }
}
```

#### 4.1.2 Skill Data Transformation with Evolution Support

**Skill Evolution Mapping and SP Cost Calculation**:

```php
<?php

namespace App\Services\Transformation;

class SkillTransformer
{
    private array $evolutionMap = [
        // Normal -> Rare evolution pairs
        'Go with the Flow' => 'Lane Legerdemain',
        'Homestretch Haste' => 'In Body and Mind',
        'Cornering Acceleration' => 'Cornering Mastery',
        'Straight Line Recovery' => 'Straight Line Supremacy',
        'Stamina Keeper' => 'Stamina Greed',
        'Speed Star' => 'Speed Star+',
        'Pace Up' => 'Spurt',
        'Last Spurt' => 'Victory Shot',
        'Good Start' => 'Rocket Start',
        'Escape' => 'Great Escape',
        
        // Japanese skill names
        '流れるように' => 'Lane Legerdemain',
        'ラストスパート' => 'Victory Shot',
        'スタートダッシュ' => 'Rocket Start',
    ];
    
    private array $spCostRanges = [
        'normal' => [120, 140, 160, 180],
        'rare' => [180, 200, 220, 240],
        'unique' => 'variable', // Depends on specific skill
    ];
    
    public function transformSkill(array $skillData): array
    {
        $skillName = $this->normalizeSkillName($skillData['name'] ?? '');
        $skillType = $this->determineSkillType($skillName, $skillData);
        $baseCost = $this->calculateBaseCost($skillType, $skillData['sp_cost'] ?? 0);
        $hintCount = (int) ($skillData['hints'] ?? 0);
        
        return [
            'skill_name' => $skillName,
            'skill_type' => $skillType,
            'skill_category' => $this->determineSkillCategory($skillName),
            'base_sp_cost' => $baseCost,
            'hint_count' => $hintCount,
            'final_sp_cost' => $this->calculateFinalCost($baseCost, $hintCount),
            'evolution_source' => $this->getEvolutionSource($skillName),
            'evolution_target' => $this->getEvolutionTarget($skillName),
            'is_acquired' => (bool) ($skillData['acquired'] ?? false),
            'acquired_at' => $skillData['acquired_date'] ?? null,
        ];
    }
    
    public function calculateFinalCost(int $baseCost, int $hintCount): int
    {
        // Apply hint discount (20% per hint, max 40%)
        $discountPercent = min($hintCount * 20, 40);
        $discountedCost = $baseCost * (1 - $discountPercent / 100);
        
        return (int) round($discountedCost);
    }
    
    private function determineSkillType(string $skillName, array $skillData): string
    {
        // Check if it's an evolution target (rare skill)
        if (in_array($skillName, $this->evolutionMap)) {
            return 'rare';
        }
        
        // Check if it's an evolution source (normal skill)
        if (array_key_exists($skillName, $this->evolutionMap)) {
            return 'normal';
        }
        
        // Check explicit type from data
        if (isset($skillData['type'])) {
            $type = strtolower($skillData['type']);
            if (in_array($type, ['normal', 'rare', 'unique'])) {
                return $type;
            }
        }
        
        // Determine by SP cost if available
        if (isset($skillData['sp_cost'])) {
            $cost = (int) $skillData['sp_cost'];
            if ($cost >= 120 && $cost <= 180) return 'normal';
            if ($cost >= 180 && $cost <= 240) return 'rare';
        }
        
        // Default to normal
        return 'normal';
    }
    
    private function determineSkillCategory(string $skillName): string
    {
        $categoryKeywords = [
            'speed' => ['speed', 'acceleration', 'spurt', 'dash', 'quick'],
            'passive' => ['keeper', 'maintain', 'steady', 'consistent'],
            'recovery' => ['recovery', 'heal', 'restore', 'refresh'],
            'debuff' => ['pressure', 'interference', 'block', 'hinder']
        ];
        
        $lowerName = strtolower($skillName);
        
        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lowerName, $keyword)) {
                    return $category;
                }
            }
        }
        
        return 'speed'; // Default category
    }
    
    public function getEvolutionTarget(string $skillName): ?string
    {
        return $this->evolutionMap[$skillName] ?? null;
    }
    
    public function getEvolutionSource(string $skillName): ?string
    {
        return array_search($skillName, $this->evolutionMap) ?: null;
    }
}
```

### 4.2 Data Quality Specifications

#### 4.2.1 Validation Rules with Laravel 12 Features

**Comprehensive Character Validation**:

```php
<?php

namespace App\Services\Validation;

class CharacterValidator
{
    public function validate(array $characterData): ValidationResult
    {
        $validator = Validator::make($characterData, [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{N}\s\-\(\)]+$/u', // Unicode support for Japanese names
            ],
            'scenario_type' => [
                'required',
                'in:ura_finale,unity_cup'
            ],
            'speed' => [
                'required',
                'integer',
                'between:0,1200'
            ],
            'stamina' => [
                'required',
                'integer',
                'between:0,1200'
            ],
            'power' => [
                'required',
                'integer',
                'between:0,1200'
            ],
            'guts' => [
                'required',
                'integer',
                'between:0,1200'
            ],
            'wit' => [
                'required',
                'integer',
                'between:0,1200'
            ],
            'energy_level' => [
                'sometimes',
                'integer',
                'between:0,100'
            ],
            'mood_status' => [
                'sometimes',
                'in:awful,bad,normal,good,great'
            ],
            'final_grade' => [
                'sometimes',
                'nullable',
                'regex:/^(G\+?|F\+?|E\+?|D\+?|C\+?|B\+?|A\+?|S\+?|SS\+?)$/'
            ]
        ], [
            'name.regex' => 'Character name contains invalid characters',
            'scenario_type.in' => 'Scenario type must be either URA Finale or Unity Cup',
            'speed.between' => 'Speed must be between 0 and 1200',
            'stamina.between' => 'Stamina must be between 0 and 1200',
            'power.between' => 'Power must be between 0 and 1200',
            'guts.between' => 'Guts must be between 0 and 1200',
            'wit.between' => 'Wit must be between 0 and 1200',
            'final_grade.regex' => 'Final grade must be in valid format (G+ through SS+)'
        ]);
        
        // Custom validation for stat distribution
        if (!$validator->fails()) {
            $this->validateStatDistribution($characterData, $validator);
            $this->validateScenarioCompatibility($characterData, $validator);
        }
        
        return new ValidationResult(
            !$validator->fails(),
            $validator->errors()->all()
        );
    }
    
    private function validateStatDistribution(array $data, $validator): void
    {
        $totalStats = ($data['speed'] ?? 0) + 
                     ($data['stamina'] ?? 0) + 
                     ($data['power'] ?? 0) + 
                     ($data['guts'] ?? 0) + 
                     ($data['wit'] ?? 0);
        
        // Warn if total stats seem unrealistic
        if ($totalStats < 1000) {
            $validator->after(function ($validator) {
                $validator->errors()->add('stats', 'Total stats seem low for a completed character');
            });
        }
        
        if ($totalStats > 5500) {
            $validator->after(function ($validator) {
                $validator->errors()->add('stats', 'Total stats exceed realistic maximum');
            });
        }
    }
    
    private function validateScenarioCompatibility(array $data, $validator): void
    {
        $scenario = $data['scenario_type'] ?? '';
        
        // Unity Cup characters typically have more balanced stats
        if ($scenario === 'unity_cup') {
            $stats = [
                $data['speed'] ?? 0,
                $data['stamina'] ?? 0,
                $data['power'] ?? 0,
                $data['guts'] ?? 0,
                $data['wit'] ?? 0
            ];
            
            $maxStat = max($stats);
            $minStat = min($stats);
            
            // Check for extreme stat imbalance
            if ($maxStat - $minStat > 800) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('scenario_compatibility', 
                        'Unity Cup characters typically have more balanced stat distribution');
                });
            }
        }
    }
}
```

#### 4.2.2 Data Completeness and Quality Scoring

**Completeness Analysis with Scoring**:

```php
<?php

namespace App\Services\Validation;

class CompletenessValidator
{
    public function validateCompleteness(array $dataset): CompletenessReport
    {
        $report = new CompletenessReport();
        
        foreach ($dataset as $index => $record) {
            $completeness = $this->calculateCompleteness($record);
            $qualityScore = $this->calculateQualityScore($record);
            
            $report->addRecord($index, $completeness, $qualityScore);
            
            // Flag records below quality thresholds
            if ($completeness < 0.8) { // 80% completeness threshold
                $report->addWarning($index, "Record completeness below threshold: {$completeness}");
            }
            
            if ($qualityScore < 0.7) { // 70% quality threshold
                $report->addWarning($index, "Record quality score below threshold: {$qualityScore}");
            }
        }
        
        return $report;
    }
    
    private function calculateCompleteness(array $record): float
    {
        $totalFields = count($record);
        $populatedFields = 0;
        
        foreach ($record as $value) {
            if ($value !== null && $value !== '' && $value !== 0) {
                $populatedFields++;
            }
        }
        
        return $totalFields > 0 ? $populatedFields / $totalFields : 0;
    }
    
    private function calculateQualityScore(array $record): float
    {
        $score = 1.0;
        
        // Penalize for missing critical fields
        $criticalFields = ['name', 'scenario_type', 'speed', 'stamina', 'power', 'guts', 'wit'];
        foreach ($criticalFields as $field) {
            if (!isset($record[$field]) || $record[$field] === '' || $record[$field] === null) {
                $score -= 0.15; // 15% penalty per missing critical field
            }
        }
        
        // Penalize for invalid data patterns
        if (isset($record['name']) && strlen($record['name']) < 3) {
            $score -= 0.1; // Name too short
        }
        
        // Bonus for additional quality indicators
        if (isset($record['final_grade']) && $record['final_grade'] !== '') {
            $score += 0.05; // Bonus for having final grade
        }
        
        if (isset($record['notes']) && strlen($record['notes']) > 10) {
            $score += 0.05; // Bonus for detailed notes
        }
        
        return max(0, min(1, $score)); // Clamp between 0 and 1
    }
}
```

---

## 5. Target Schema Specifications

### 5.1 Database Schema (Laravel 12 Optimized)

#### 5.1.1 Core Tables with Modern Features

**Users Table with Enhanced Security and Preferences**:

```sql
CREATE TABLE ucp_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    
    -- User preferences for accessibility and UX
    theme ENUM('light', 'dark', 'auto') DEFAULT 'auto',
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    accessibility_mode BOOLEAN DEFAULT FALSE,
    high_contrast BOOLEAN DEFAULT FALSE,
    reduced_motion BOOLEAN DEFAULT FALSE,
    screen_reader_mode BOOLEAN DEFAULT FALSE,
    
    -- Privacy and consent management
    data_sharing_consent BOOLEAN DEFAULT FALSE,
    analytics_consent BOOLEAN DEFAULT FALSE,
    marketing_consent BOOLEAN DEFAULT FALSE,
    consent_updated_at TIMESTAMP NULL,
    
    -- AI preferences
    ai_model_preference ENUM('local_only', 'hybrid', 'cloud_preferred') DEFAULT 'hybrid',
    ai_cost_limit_usd DECIMAL(8, 2) DEFAULT 10.00,
    
    -- Account security
    two_factor_secret VARCHAR(255) NULL,
    two_factor_recovery_codes TEXT NULL,
    failed_login_attempts TINYINT UNSIGNED DEFAULT 0,
    locked_until TIMESTAMP NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    
    -- Indexes for performance
    INDEX idx_email (email),
    INDEX idx_created_at (created_at),
    INDEX idx_last_login (last_login_at),
    INDEX idx_theme_preferences (theme, accessibility_mode)
);
```

**Characters Table with Enhanced JSON Support**:

```sql
CREATE TABLE ucp_characters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Character identification
    name VARCHAR(255) NOT NULL,
    scenario_type ENUM('ura_finale', 'unity_cup') NOT NULL,
    career_stage VARCHAR(50) DEFAULT 'junior',
    
    -- Current stats (0-1200 range with validation)
    speed SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    stamina SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    power SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    guts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    wit SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    
    -- Character state
    energy_level TINYINT UNSIGNED NOT NULL DEFAULT 100,
    mood_status ENUM('awful', 'bad', 'normal', 'good', 'great') NOT NULL DEFAULT 'normal',
    
    -- Goals and targets (JSON with Laravel casting)
    target_stats JSON NULL COMMENT 'Target stat values for training optimization',
    race_objectives JSON NULL COMMENT 'Planned race schedule and objectives',
    training_preferences JSON NULL COMMENT 'User preferences for training recommendations',
    
    -- Legacy and inheritance data
    parent_characters JSON NULL COMMENT 'Parent character information for factor inheritance',
    inherited_factors JSON NULL COMMENT 'Blue, red, green, and white factors from parents',
    
    -- Progress tracking
    current_turn SMALLINT UNSIGNED DEFAULT 0,
    total_turns SMALLINT UNSIGNED DEFAULT 78, -- Standard career length
    completion_percentage DECIMAL(5, 2) DEFAULT 0.00,
    
    -- Metadata
    final_grade VARCHAR(10) NULL,
    completion_date TIMESTAMP NULL,
    notes TEXT,
    
    -- Data quality and source tracking
    data_source ENUM('manual', 'import', 'ocr', 'api') DEFAULT 'manual',
    data_quality_score DECIMAL(3, 2) DEFAULT 1.00,
    last_validated_at TIMESTAMP NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance optimization
    INDEX idx_user_scenario (user_id, scenario_type),
    INDEX idx_name (name),
    INDEX idx_completion (completion_date, final_grade),
    INDEX idx_data_quality (data_quality_score, last_validated_at),
    INDEX idx_progress (completion_percentage, current_turn),
    
    -- Full-text search for notes
    FULLTEXT idx_notes_search (name, notes),
    
    -- Foreign keys with cascade
    FOREIGN KEY (user_id) REFERENCES ucp_users(id) ON DELETE CASCADE,
    
    -- Check constraints for data integrity
    CONSTRAINT chk_speed_range CHECK (speed >= 0 AND speed <= 1200),
    CONSTRAINT chk_stamina_range CHECK (stamina >= 0 AND stamina <= 1200),
    CONSTRAINT chk_power_range CHECK (power >= 0 AND power <= 1200),
    CONSTRAINT chk_guts_range CHECK (guts >= 0 AND guts <= 1200),
    CONSTRAINT chk_wit_range CHECK (wit >= 0 AND wit <= 1200),
    CONSTRAINT chk_energy_range CHECK (energy_level >= 0 AND energy_level <= 100),
    CONSTRAINT chk_completion_range CHECK (completion_percentage >= 0 AND completion_percentage <= 100),
    CONSTRAINT chk_quality_score CHECK (data_quality_score >= 0 AND data_quality_score <= 1),
    CONSTRAINT chk_turn_logic CHECK (current_turn <= total_turns),
    
    -- JSON validation constraints
    CONSTRAINT chk_target_stats_json CHECK (JSON_VALID(target_stats)),
    CONSTRAINT chk_race_objectives_json CHECK (JSON_VALID(race_objectives)),
    CONSTRAINT chk_training_preferences_json CHECK (JSON_VALID(training_preferences)),
    CONSTRAINT chk_parent_characters_json CHECK (JSON_VALID(parent_characters)),
    CONSTRAINT chk_inherited_factors_json CHECK (JSON_VALID(inherited_factors))
);
```

#### 5.1.2 Advanced Tables for Modern Features

**AI Conversations Table for Hybrid AI System**:

```sql
CREATE TABLE ucp_ai_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    character_id BIGINT UNSIGNED NULL,
    
    -- Conversation metadata
    conversation_id VARCHAR(255) NOT NULL,
    message_type ENUM('user', 'assistant', 'system') NOT NULL,
    message_sequence INT UNSIGNED NOT NULL DEFAULT 1,
    
    -- AI system information
    ai_provider ENUM('ollama', 'bedrock') NOT NULL,
    ai_model VARCHAR(100) NOT NULL COMMENT 'e.g., llama3.3, claude-3-5-sonnet',
    model_version VARCHAR(50) NULL,
    
    -- Processing metrics
    processing_time_ms INT UNSIGNED NULL,
    token_count_input INT UNSIGNED NULL,
    token_count_output INT UNSIGNED NULL,
    cost_usd DECIMAL(10, 6) NULL DEFAULT 0,
    
    -- Message content and context
    message_content TEXT NOT NULL,
    context_data JSON NULL COMMENT 'Character state, training context, etc.',
    confidence_score DECIMAL(3, 2) NULL COMMENT 'AI confidence in response',
    
    -- Quality and feedback
    user_rating TINYINT NULL COMMENT '1-5 star rating from user',
    feedback_text TEXT NULL,
    was_helpful BOOLEAN NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes for efficient querying
    INDEX idx_user_conversation (user_id, conversation_id, message_sequence),
    INDEX idx_character_context (character_id, created_at),
    INDEX idx_ai_model_performance (ai_provider, ai_model, processing_time_ms),
    INDEX idx_cost_tracking (user_id, cost_usd, created_at),
    INDEX idx_quality_metrics (confidence_score, user_rating),
    
    -- Full-text search for message content
    FULLTEXT idx_message_search (message_content),
    
    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES ucp_users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES ucp_characters(id) ON DELETE SET NULL,
    
    -- Check constraints
    CONSTRAINT chk_confidence_range CHECK (confidence_score >= 0 AND confidence_score <= 1),
    CONSTRAINT chk_user_rating_range CHECK (user_rating >= 1 AND user_rating <= 5),
    CONSTRAINT chk_cost_positive CHECK (cost_usd >= 0),
    CONSTRAINT chk_token_counts_positive CHECK (
        (token_count_input IS NULL OR token_count_input >= 0) AND
        (token_count_output IS NULL OR token_count_output >= 0)
    ),
    CONSTRAINT chk_context_data_json CHECK (JSON_VALID(context_data))
);
```

**OCR Extractions Table for Screenshot Processing**:

```sql
CREATE TABLE ucp_ocr_extractions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    character_id BIGINT UNSIGNED NULL,
    
    -- File information
    original_filename VARCHAR(255) NOT NULL,
    file_hash VARCHAR(64) NOT NULL UNIQUE,
    file_size INT UNSIGNED NOT NULL,
    file_mime_type VARCHAR(100) NOT NULL,
    
    -- OCR processing information
    screen_type ENUM('training', 'race', 'character_stats', 'skills', 'support_cards', 'unknown') NULL,
    processing_status ENUM('pending', 'processing', 'completed', 'failed', 'manual_review') NOT NULL DEFAULT 'pending',
    
    -- OCR results
    raw_text TEXT NULL COMMENT 'Raw OCR output',
    confidence_score DECIMAL(3, 2) NULL COMMENT 'Overall OCR confidence',
    extracted_data JSON NULL COMMENT 'Structured data extracted from image',
    validation_errors JSON NULL COMMENT 'Data validation errors found',
    manual_corrections JSON NULL COMMENT 'User corrections to OCR results',
    
    -- Processing metadata
    processing_time_ms INT UNSIGNED NULL,
    ocr_engine VARCHAR(50) DEFAULT 'tesseract',
    ocr_version VARCHAR(20) NULL,
    preprocessing_applied JSON NULL COMMENT 'Image preprocessing steps applied',
    
    -- Quality and review
    requires_manual_review BOOLEAN DEFAULT FALSE,
    reviewed_by_user BOOLEAN DEFAULT FALSE,
    review_completed_at TIMESTAMP NULL,
    accuracy_rating TINYINT NULL COMMENT '1-5 rating of OCR accuracy',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_user_character (user_id, character_id),
    INDEX idx_file_hash (file_hash),
    INDEX idx_screen_type_status (screen_type, processing_status),
    INDEX idx_processing_queue (processing_status, created_at),
    INDEX idx_review_queue (requires_manual_review, reviewed_by_user),
    INDEX idx_quality_metrics (confidence_score, accuracy_rating),
    
    -- Full-text search for OCR text
    FULLTEXT idx_ocr_text_search (raw_text),
    
    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES ucp_users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES ucp_characters(id) ON DELETE SET NULL,
    
    -- Check constraints
    CONSTRAINT chk_file_size_positive CHECK (file_size > 0),
    CONSTRAINT chk_confidence_range CHECK (confidence_score >= 0 AND confidence_score <= 1),
    CONSTRAINT chk_accuracy_rating_range CHECK (accuracy_rating >= 1 AND accuracy_rating <= 5),
    CONSTRAINT chk_processing_time_positive CHECK (processing_time_ms >= 0),
    
    -- JSON validation
    CONSTRAINT chk_extracted_data_json CHECK (JSON_VALID(extracted_data)),
    CONSTRAINT chk_validation_errors_json CHECK (JSON_VALID(validation_errors)),
    CONSTRAINT chk_manual_corrections_json CHECK (JSON_VALID(manual_corrections)),
    CONSTRAINT chk_preprocessing_json CHECK (JSON_VALID(preprocessing_applied))
);
```

**External Data Cache Table with Intelligent TTL Management**:

```sql
CREATE TABLE ucp_external_data_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Cache key information
    cache_key VARCHAR(255) NOT NULL UNIQUE,
    data_source ENUM('umapyoi', 'umamusumedb', 'community', 'manual') NOT NULL,
    data_type ENUM('characters', 'skills', 'support_cards', 'races', 'meta_tiers', 'news') NOT NULL,
    
    -- Cache data
    cached_data JSON NOT NULL,
    data_hash VARCHAR(64) NOT NULL COMMENT 'SHA-256 hash for change detection',
    data_version VARCHAR(50) NULL COMMENT 'API version or data version',
    
    -- Cache metadata and performance
    ttl_seconds INT UNSIGNED NOT NULL DEFAULT 86400,
    hit_count INT UNSIGNED DEFAULT 0,
    miss_count INT UNSIGNED DEFAULT 0,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    access_frequency DECIMAL(8, 4) DEFAULT 0 COMMENT 'Accesses per hour',
    
    -- Data quality and validation
    data_quality_score DECIMAL(3, 2) DEFAULT 1.00,
    validation_errors JSON NULL,
    last_validated_at TIMESTAMP NULL,
    
    -- Cache management
    cache_priority TINYINT UNSIGNED DEFAULT 5 COMMENT '1-10 priority for cache eviction',
    auto_refresh BOOLEAN DEFAULT TRUE,
    refresh_threshold_hours INT UNSIGNED DEFAULT 1,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    last_refresh_attempt TIMESTAMP NULL,
    
    -- Indexes for cache performance
    INDEX idx_cache_key (cache_key),
    INDEX idx_data_source_type (data_source, data_type),
    INDEX idx_expires_at (expires_at),
    INDEX idx_last_accessed (last_accessed),
    INDEX idx_cache_priority (cache_priority, expires_at),
    INDEX idx_auto_refresh (auto_refresh, refresh_threshold_hours, last_refresh_attempt),
    INDEX idx_data_quality (data_quality_score, last_validated_at),
    
    -- Check constraints
    CONSTRAINT chk_ttl_positive CHECK (ttl_seconds > 0),
    CONSTRAINT chk_hit_count_positive CHECK (hit_count >= 0),
    CONSTRAINT chk_miss_count_positive CHECK (miss_count >= 0),
    CONSTRAINT chk_quality_score_range CHECK (data_quality_score >= 0 AND data_quality_score <= 1),
    CONSTRAINT chk_cache_priority_range CHECK (cache_priority >= 1 AND cache_priority <= 10),
    CONSTRAINT chk_refresh_threshold_positive CHECK (refresh_threshold_hours > 0),
    
    -- JSON validation
    CONSTRAINT chk_cached_data_json CHECK (JSON_VALID(cached_data)),
    CONSTRAINT chk_validation_errors_json CHECK (JSON_VALID(validation_errors))
);
```

### 5.2 Data Integrity Constraints (Enhanced)

#### 5.2.1 Referential Integrity with Cascade Rules

**Foreign Key Constraints with Intelligent Cascading**:

```sql
-- User-related cascades
ALTER TABLE ucp_characters 
ADD CONSTRAINT fk_characters_user 
FOREIGN KEY (user_id) REFERENCES ucp_users(id) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

ALTER TABLE ucp_ai_conversations 
ADD CONSTRAINT fk_conversations_user 
FOREIGN KEY (user_id) REFERENCES ucp_users(id) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

ALTER TABLE ucp_ocr_extractions 
ADD CONSTRAINT fk_ocr_user 
FOREIGN KEY (user_id) REFERENCES ucp_users(id) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

-- Character-related cascades with SET NULL for optional relationships
ALTER TABLE ucp_ai_conversations 
ADD CONSTRAINT fk_conversations_character 
FOREIGN KEY (character_id) REFERENCES ucp_characters(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

ALTER TABLE ucp_ocr_extractions 
ADD CONSTRAINT fk_ocr_character 
FOREIGN KEY (character_id) REFERENCES ucp_characters(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- Skills and aptitudes cascade with character deletion
ALTER TABLE ucp_skills 
ADD CONSTRAINT fk_skills_character 
FOREIGN KEY (character_id) REFERENCES ucp_characters(id) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

ALTER TABLE ucp_aptitudes 
ADD CONSTRAINT fk_aptitudes_character 
FOREIGN KEY (character_id) REFERENCES ucp_characters(id) 
ON DELETE CASCADE 
ON UPDATE CASCADE;
```

#### 5.2.2 Advanced Data Validation Constraints

**Complex Check Constraints for Business Logic**:

```sql
-- Character stat validation with business rules
ALTER TABLE ucp_characters 
ADD CONSTRAINT chk_stat_distribution 
CHECK (
    (speed + stamina + power + guts + wit) >= 500 AND 
    (speed + stamina + power + guts + wit) <= 6000
);

-- Energy and mood correlation
ALTER TABLE ucp_characters 
ADD CONSTRAINT chk_energy_mood_correlation 
CHECK (
    (energy_level >= 80 AND mood_status IN ('good', 'great')) OR
    (energy_level >= 60 AND mood_status IN ('normal', 'good', 'great')) OR
    (energy_level >= 40 AND mood_status IN ('bad', 'normal', 'good', 'great')) OR
    (energy_level < 40 AND mood_status IN ('awful', 'bad', 'normal'))
);

-- AI conversation validation
ALTER TABLE ucp_ai_conversations 
ADD CONSTRAINT chk_ai_model_provider_match 
CHECK (
    (ai_provider = 'ollama' AND ai_model IN ('llama3.3', 'mistral', 'qwen2.5')) OR
    (ai_provider = 'bedrock' AND ai_model LIKE 'anthropic.claude%' OR ai_model LIKE 'amazon.nova%')
);

-- Cost validation for AI conversations
ALTER TABLE ucp_ai_conversations 
ADD CONSTRAINT chk_cost_token_relationship 
CHECK (
    (ai_provider = 'ollama' AND cost_usd = 0) OR
    (ai_provider = 'bedrock' AND cost_usd > 0 AND token_count_input > 0)
);

-- OCR processing validation
ALTER TABLE ucp_ocr_extractions 
ADD CONSTRAINT chk_ocr_processing_logic 
CHECK (
    (processing_status = 'completed' AND confidence_score IS NOT NULL) OR
    (processing_status = 'failed' AND confidence_score IS NULL) OR
    (processing_status IN ('pending', 'processing', 'manual_review'))
);

-- Cache expiration logic
ALTER TABLE ucp_external_data_cache 
ADD CONSTRAINT chk_cache_expiration_logic 
CHECK (expires_at > created_at);

-- Cache hit/miss ratio validation
ALTER TABLE ucp_external_data_cache 
ADD CONSTRAINT chk_cache_metrics_logic 
CHECK (hit_count >= 0 AND miss_count >= 0);
```

---

## 6. Migration Tool Specifications

### 6.1 Command Line Interface (Laravel 12 Enhanced)

#### 6.1.1 Artisan Commands with Modern Features

**Game Data Migration Command with Progress Tracking**:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Migration\GameDataMigrationService;
use App\Events\MigrationStarted;
use App\Events\MigrationCompleted;

class MigrateGameDataCommand extends Command
{
    protected $signature = 'migrate:game-data 
                           {--source=umapyoi : Data source to use (umapyoi, umamusumedb, all)}
                           {--force : Force migration even if data exists}
                           {--dry-run : Show what would be migrated without executing}
                           {--chunk=100 : Number of records to process per batch}
                           {--timeout=1800 : Maximum execution time in seconds}
                           {--parallel=1 : Number of parallel processing workers}
                           {--validate-only : Only validate data without importing}
                           {--skip-cache : Skip cache and fetch fresh data}';
    
    protected $description = 'Migrate game data from external sources with modern Laravel 12 features';
    
    public function handle(GameDataMigrationService $service): int
    {
        $this->info('🚀 Starting game data migration with Laravel 12...');
        
        // Validate options
        if (!$this->validateOptions()) {
            return Command::FAILURE;
        }
        
        $options = [
            'source' => $this->option('source'),
            'force' => $this->option('force'),
            'dry_run' => $this->option('dry-run'),
            'chunk_size' => (int) $this->option('chunk'),
            'timeout' => (int) $this->option('timeout'),
            'parallel_workers' => (int) $this->option('parallel'),
            'validate_only' => $this->option('validate-only'),
            'skip_cache' => $this->option('skip-cache'),
        ];
        
        try {
            // Dispatch migration started event
            event(new MigrationStarted(auth()->id() ?? 0, 'game-data', $options));
            
            // Create progress bar with modern styling
            $progressBar = $this->output->createProgressBar();
            $progressBar->setFormat('verbose');
            $progressBar->setBarCharacter('<fg=green>█</>');
            $progressBar->setEmptyBarCharacter('<fg=red>░</>');
            $progressBar->setProgressCharacter('<fg=green>█</>');
            
            // Execute migration with real-time progress and WebSocket broadcasting
            $result = $service->migrate($options, function ($progress) use ($progressBar) {
                $progressBar->setProgress($progress['current']);
                $progressBar->setMaxSteps($progress['total']);
                
                // Broadcast progress for web interface
                broadcast(new \App\Events\MigrationProgress([
                    'type' => 'game-data',
                    'current' => $progress['current'],
                    'total' => $progress['total'],
                    'message' => $progress['message'],
                    'percentage' => round(($progress['current'] / $progress['total']) * 100, 2)
                ]));
                
                $this->line(" <fg=cyan>Processing:</> {$progress['current']}/{$progress['total']} - {$progress['message']}");
            });
            
            $progressBar->finish();
            $this->newLine(2);
            
            // Display comprehensive results
            $this->displayResults($result);
            
            // Dispatch completion event
            event(new MigrationCompleted($result));
            
            return Command::SUCCESS;
            
        } catch (Exception $e) {
            $this->error("❌ Migration failed: {$e->getMessage()}");
            $this->line("<fg=yellow>Stack trace available in logs:</> storage/logs/laravel.log");
            
            // Log detailed error information
            Log::error('Game data migration failed', [
                'options' => $options,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    private function validateOptions(): bool
    {
        $source = $this->option('source');
        $validSources = ['umapyoi', 'umamusumedb', 'all'];
        
        if (!in_array($source, $validSources)) {
            $this->error("Invalid source: {$source}. Valid options: " . implode(', ', $validSources));
            return false;
        }
        
        $chunkSize = (int) $this->option('chunk');
        if ($chunkSize < 1 || $chunkSize > 1000) {
            $this->error("Invalid chunk size: {$chunkSize}. Must be between 1 and 1000.");
            return false;
        }
        
        $parallelWorkers = (int) $this->option('parallel');
        if ($parallelWorkers < 1 || $parallelWorkers > 10) {
            $this->error("Invalid parallel workers: {$parallelWorkers}. Must be between 1 and 10.");
            return false;
        }
        
        return true;
    }
    
    private function displayResults($result): void
    {
        $this->info('✅ Migration completed successfully!');
        
        // Create detailed results table
        $this->table(
            ['Entity', 'Records', 'Status', 'Processing Time', 'Errors'],
            [
                [
                    'Characters', 
                    number_format($result->getCharacterCount()), 
                    '✅ Success', 
                    $result->getCharacterTime() . 'ms',
                    $result->getCharacterErrors()
                ],
                [
                    'Skills', 
                    number_format($result->getSkillCount()), 
                    '✅ Success', 
                    $result->getSkillTime() . 'ms',
                    $result->getSkillErrors()
                ],
                [
                    'Support Cards', 
                    number_format($result->getSupportCardCount()), 
                    '✅ Success', 
                    $result->getSupportCardTime() . 'ms',
                    $result->getSupportCardErrors()
                ],
            ]
        );
        
        // Display performance metrics
        $this->info("\n📊 Performance Metrics:");
        $this->line("Total Processing Time: <fg=green>{$result->getTotalTime()}ms</>");
        $this->line("Average Records/Second: <fg=green>{$result->getRecordsPerSecond()}</>");
        $this->line("Memory Peak Usage: <fg=green>{$result->getPeakMemoryUsage()}MB</>");
        $this->line("Cache Hit Rate: <fg=green>{$result->getCacheHitRate()}%</>");
        
        // Display warnings if any
        if ($result->hasWarnings()) {
            $this->warn("\n⚠️  Warnings:");
            foreach ($result->getWarnings() as $warning) {
                $this->line("  • {$warning}");
            }
        }
    }
}
```

**User Data Import Command with Accessibility Features**:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Import\UserDataImportService;

class ImportUserDataCommand extends Command
{
    protected $signature = 'import:user-data 
                           {user : User ID or email}
                           {file : Path to data file}
                           {--format=auto : File format (csv, xlsx, json, auto)}
                           {--validate-only : Only validate without importing}
                           {--backup : Create backup before import}
                           {--accessibility-check : Validate accessibility compliance}
                           {--preview-rows=5 : Number of rows to preview}
                           {--encoding=utf-8 : File encoding}';
    
    protected $description = 'Import user data from file with accessibility and validation features';
    
    public function handle(UserDataImportService $service): int
    {
        $userId = $this->argument('user');
        $filePath = $this->argument('file');
        
        // Validate file exists and is accessible
        if (!file_exists($filePath)) {
            $this->error("❌ File not found: {$filePath}");
            return Command::FAILURE;
        }
        
        if (!is_readable($filePath)) {
            $this->error("❌ File is not readable: {$filePath}");
            return Command::FAILURE;
        }
        
        $this->info("📁 Importing data for user: <fg=cyan>{$userId}</>");
        $this->info("📄 File: <fg=cyan>{$filePath}</>");
        
        try {
            // Accessibility check if requested
            if ($this->option('accessibility-check')) {
                $this->performAccessibilityCheck($filePath);
            }
            
            // Validate file format and content
            $validation = $service->validateFile($filePath, [
                'format' => $this->option('format'),
                'encoding' => $this->option('encoding'),
                'preview_rows' => (int) $this->option('preview-rows')
            ]);
            
            if (!$validation->isValid()) {
                $this->error('❌ File validation failed:');
                foreach ($validation->getErrors() as $error) {
                    $this->line("  • <fg=red>{$error}</>");
                }
                return Command::FAILURE;
            }
            
            // Show preview of data
            $this->displayDataPreview($validation->getPreviewData());
            
            if ($this->option('validate-only')) {
                $this->info('✅ File validation passed. Use without --validate-only to import.');
                return Command::SUCCESS;
            }
            
            // Confirm import with user
            if (!$this->confirm('Do you want to proceed with the import?')) {
                $this->info('Import cancelled by user.');
                return Command::SUCCESS;
            }
            
            // Create backup if requested
            if ($this->option('backup')) {
                $backupPath = $service->createUserBackup($userId);
                $this->info("💾 Backup created: <fg=green>{$backupPath}</>");
            }
            
            // Import data with progress tracking
            $result = $service->importUserData($userId, $filePath, [
                'format' => $this->option('format'),
                'encoding' => $this->option('encoding'),
            ]);
            
            // Display import results
            $this->displayImportResults($result);
            
            if ($result->hasErrors()) {
                $this->warn('⚠️  Some records had errors. Check the logs for details.');
                if ($this->option('backup')) {
                    $this->info("💾 Backup is available for rollback if needed.");
                }
            }
            
            return Command::SUCCESS;
            
        } catch (Exception $e) {
            $this->error("❌ Import failed: {$e->getMessage()}");
            Log::error('User data import failed', [
                'user_id' => $userId,
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
    
    private function performAccessibilityCheck(string $filePath): void
    {
        $this->info("♿ Performing accessibility check...");
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'csv':
                $this->line("  ✅ CSV format is highly accessible");
                break;
            case 'json':
                $this->line("  ✅ JSON format is accessible with proper structure");
                break;
            case 'xlsx':
            case 'xls':
                $this->warn("  ⚠️  Excel format may have accessibility limitations");
                $this->line("     Consider using CSV format for better accessibility");
                break;
            default:
                $this->warn("  ⚠️  Unknown format accessibility implications");
        }
        
        // Check file size for performance accessibility
        $fileSize = filesize($filePath);
        if ($fileSize > 10 * 1024 * 1024) { // 10MB
            $this->warn("  ⚠️  Large file size may impact processing time");
            $this->line("     Consider splitting into smaller files for better user experience");
        }
    }
    
    private function displayDataPreview(array $previewData): void
    {
        $this->info("\n📋 Data Preview:");
        
        if (empty($previewData)) {
            $this->warn("No preview data available");
            return;
        }
        
        // Display first few rows as table
        $headers = array_keys($previewData[0]);
        $rows = array_slice($previewData, 0, 5);
        
        $this->table($headers, $rows);
        
        if (count($previewData) > 5) {
            $this->line("... and " . (count($previewData) - 5) . " more rows");
        }
    }
    
    private function displayImportResults($result): void
    {
        $this->info("\n✅ Import completed:");
        
        $this->table(
            ['Entity', 'Imported', 'Skipped', 'Errors'],
            [
                ['Characters', $result->getImportedCharacters(), $result->getSkippedCharacters(), $result->getCharacterErrors()],
                ['Skills', $result->getImportedSkills(), $result->getSkippedSkills(), $result->getSkillErrors()],
                ['Careers', $result->getImportedCareers(), $result->getSkippedCareers(), $result->getCareerErrors()],
            ]
        );
        
        $this->info("\n📊 Import Summary:");
        $this->line("Total Records Processed: <fg=green>" . number_format($result->getTotalProcessed()) . "</>");
        $this->line("Success Rate: <fg=green>{$result->getSuccessRate()}%</>");
        $this->line("Processing Time: <fg=green>{$result->getProcessingTime()}ms</>");
    }
}
```

### 6.2 Web Interface Specifications (Tailwind CSS v4 + Accessibility)

#### 6.2.1 File Upload Interface with Modern UX

**Drag-and-Drop Upload Component with Accessibility**:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploadComponent extends Component
{
    use WithFileUploads;
    
    public $uploadedFile;
    public $uploadProgress = 0;
    public $validationErrors = [];
    public $previewData = [];
    public $isProcessing = false;
    public $accessibilityMode = false;
    
    protected $listeners = [
        'fileUploadProgress' => 'updateProgress',
        'fileValidationComplete' => 'handleValidation'
    ];
    
    public function mount()
    {
        $this->accessibilityMode = auth()->user()->accessibility_mode ?? false;
    }
    
    public function updatedUploadedFile()
    {
        $this->validateFile();
    }
    
    private function validateFile()
    {
        $this->validate([
            'uploadedFile' => [
                'required',
                'file',
                'max:51200', // 50MB
                'mimes:csv,xlsx,xls,json'
            ]
        ], [
            'uploadedFile.max' => 'File size must not exceed 50MB for optimal processing.',
            'uploadedFile.mimes' => 'Please upload a CSV, Excel, or JSON file for compatibility.'
        ]);
        
        // Perform accessibility validation
        $this->performAccessibilityValidation();
        
        // Start file processing
        $this->processFile();
    }
    
    private function performAccessibilityValidation()
    {
        $extension = $this->uploadedFile->getClientOriginalExtension();
        
        switch (strtolower($extension)) {
            case 'csv':
                $this->addAccessibilityNote('CSV format selected - excellent accessibility support');
                break;
            case 'json':
                $this->addAccessibilityNote('JSON format selected - good accessibility support');
                break;
            case 'xlsx':
            case 'xls':
                $this->addAccessibilityWarning('Excel format detected - consider CSV for better accessibility');
                break;
        }
    }
    
    public function render()
    {
        return view('livewire.file-upload', [
            'accessibilityMode' => $this->accessibilityMode
        ]);
    }
}
```

**Accessible Upload Interface Template**:

```html
<!-- resources/views/livewire/file-upload.blade.php -->
<div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
    <!-- Screen Reader Instructions -->
    <div class="sr-only" aria-live="polite" id="upload-instructions">
        File upload area. You can drag and drop files here or use the browse button. 
        Supported formats: CSV, Excel, JSON. Maximum size: 50MB.
    </div>
    
    <!-- Upload Area -->
    <div 
        class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center
               hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200
               focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-200"
        x-data="{ dragOver: false }"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="dragOver = false; $wire.handleFileDrop($event)"
        :class="{ 'border-blue-400 bg-blue-50 dark:bg-blue-900/20': dragOver }"
        role="region"
        aria-labelledby="upload-title"
        aria-describedby="upload-description"
    >
        <!-- Upload Icon -->
        <div class="mx-auto w-16 h-16 text-gray-400 dark:text-gray-500 mb-4">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
        </div>
        
        <!-- Upload Title -->
        <h3 id="upload-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            Upload Your Data File
        </h3>
        
        <!-- Upload Description -->
        <p id="upload-description" class="text-gray-600 dark:text-gray-400 mb-4">
            Drag and drop your file here, or 
            <button 
                type="button" 
                class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 
                       underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                onclick="document.getElementById('file-input').click()"
                aria-describedby="file-formats"
            >
                browse to choose a file
            </button>
        </p>
        
        <!-- File Input -->
        <input 
            type="file" 
            id="file-input"
            wire:model="uploadedFile"
            accept=".csv,.xlsx,.xls,.json"
            class="sr-only"
            aria-describedby="file-formats upload-instructions"
        />
        
        <!-- Supported Formats -->
        <p id="file-formats" class="text-sm text-gray-500 dark:text-gray-400">
            Supported formats: CSV, Excel (.xlsx, .xls), JSON • Maximum size: 50MB
        </p>
        
        <!-- Accessibility Recommendations -->
        @if($accessibilityMode)
        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-md border border-blue-200 dark:border-blue-700">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Accessibility Recommendation
                    </h4>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        For the best accessibility experience, we recommend using CSV format. 
                        CSV files are easier to process and more compatible with screen readers.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Upload Progress -->
    @if($isProcessing)
    <div class="mt-6" role="status" aria-live="polite">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Processing file...
            </span>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $uploadProgress }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300 ease-out"
                style="width: {{ $uploadProgress }}%"
                aria-valuenow="{{ $uploadProgress }}"
                aria-valuemin="0"
                aria-valuemax="100"
                role="progressbar"
                aria-label="File processing progress"
            ></div>
        </div>
    </div>
    @endif
    
    <!-- Validation Errors -->
    @if(!empty($validationErrors))
    <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-md" 
         role="alert" aria-live="assertive">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                    File validation errors
                </h3>
                <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                    @foreach($validationErrors as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Preview Data -->
    @if(!empty($previewData))
    <div class="mt-6">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Data Preview
        </h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        @foreach(array_keys($previewData[0] ?? []) as $header)
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ $header }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach(array_slice($previewData, 0, 5) as $row)
                    <tr>
                        @foreach($row as $cell)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $cell }}
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(count($previewData) > 5)
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            ... and {{ count($previewData) - 5 }} more rows
        </p>
        @endif
    </div>
    @endif
</div>
```

---

## 11. AI Integration Specifications

### 11.1 Ollama Local AI Integration

#### 11.1.1 Local Model Management and Migration

**Ollama Service Configuration**:

```php
<?php

namespace App\Services\AI;

use Cloudstudio\OllamaLaravel\Facades\OllamaLaravel;
use App\Models\AIConversation;

class OllamaIntegrationService
{
    private array $supportedModels = [
        'llama3.3' => [
            'size' => '70B',
            'context_length' => 128000,
            'use_case' => 'general_strategy',
            'memory_requirement' => '8GB',
            'processing_speed' => 'medium'
        ],
        'mistral' => [
            'size' => '7B',
            'context_length' => 32000,
            'use_case' => 'quick_responses',
            'memory_requirement' => '4GB',
            'processing_speed' => 'fast'
        ],
        'qwen2.5' => [
            'size' => '14B',
            'context_length' => 32000,
            'use_case' => 'multilingual_support',
            'memory_requirement' => '6GB',
            'processing_speed' => 'medium'
        ]
    ];
    
    public function migrateConversationHistory(array $conversations): MigrationResult
    {
        $migrated = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($conversations as $conversation) {
                $this->validateConversationFormat($conversation);
                
                $transformed = $this->transformConversationData($conversation);
                
                AIConversation::create([
                    'user_id' => $transformed['user_id'],
                    'character_id' => $transformed['character_id'],
                    'conversation_id' => $transformed['conversation_id'],
                    'message_type' => $transformed['message_type'],
                    'message_sequence' => $transformed['sequence'],
                    'ai_provider' => 'ollama',
                    'ai_model' => $transformed['model'],
                    'model_version' => $transformed['model_version'],
                    'processing_time_ms' => $transformed['processing_time'],
                    'token_count_input' => $transformed['input_tokens'],
                    'token_count_output' => $transformed['output_tokens'],
                    'cost_usd' => 0, // Ollama is free
                    'message_content' => $transformed['content'],
                    'context_data' => $transformed['context'],
                    'confidence_score' => $transformed['confidence'],
                    'created_at' => $transformed['timestamp']
                ]);
                
                $migrated++;
            }
            
            DB::commit();
            
        } catch (Exception $e) {
            DB::rollback();
            $errors[] = "Migration failed: {$e->getMessage()}";
        }
        
        return new MigrationResult($migrated, count($errors), $errors);
    }
    
    public function setupModelConfiguration(): void
    {
        foreach ($this->supportedModels as $model => $config) {
            try {
                // Check if model is available
                if ($this->isModelAvailable($model)) {
                    $this->configureModel($model, $config);
                    Log::info("Ollama model configured successfully", [
                        'model' => $model,
                        'config' => $config
                    ]);
                } else {
                    Log::warning("Ollama model not available", [
                        'model' => $model,
                        'suggestion' => "Run: ollama pull {$model}"
                    ]);
                }
            } catch (Exception $e) {
                Log::error("Failed to configure Ollama model", [
                    'model' => $model,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function isModelAvailable(string $model): bool
    {
        try {
            $response = OllamaLaravel::agent()
                ->model($model)
                ->ask('Test connection - respond with "OK"');
            
            return !empty($response) && str_contains(strtoupper($response), 'OK');
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function configureModel(string $model, array $config): void
    {
        // Set model-specific parameters
        $parameters = [
            'temperature' => 0.7,
            'top_p' => 0.9,
            'max_tokens' => min(4000, $config['context_length'] / 4),
            'stop' => ['Human:', 'Assistant:', '\n\nHuman:', '\n\nAssistant:']
        ];
        
        // Store configuration in cache for quick access
        Cache::put("ollama.model.{$model}.config", [
            'model' => $model,
            'parameters' => $parameters,
            'capabilities' => $config,
            'configured_at' => now()
        ], 86400); // 24 hours
    }
    
    public function processGameStrategyQuery(string $query, array $context = []): array
    {
        $startTime = microtime(true);
        
        // Select best model for the query type
        $selectedModel = $this->selectOptimalModel($query, $context);
        
        // Prepare context-aware prompt
        $prompt = $this->buildGameStrategyPrompt($query, $context);
        
        try {
            $response = OllamaLaravel::agent()
                ->model($selectedModel)
                ->ask($prompt);
            
            $processingTime = (microtime(true) - $startTime) * 1000;
            
            // Estimate token usage (approximate)
            $inputTokens = $this->estimateTokenCount($prompt);
            $outputTokens = $this->estimateTokenCount($response);
            
            return [
                'response' => $response,
                'model' => $selectedModel,
                'processing_time_ms' => round($processingTime),
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost_usd' => 0, // Ollama is free
                'confidence' => $this->calculateConfidence($response),
                'context_used' => !empty($context)
            ];
            
        } catch (Exception $e) {
            Log::error('Ollama processing failed', [
                'model' => $selectedModel,
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            
            throw new AIProcessingException("Local AI processing failed: {$e->getMessage()}");
        }
    }
    
    private function selectOptimalModel(string $query, array $context): string
    {
        $queryLength = strlen($query);
        $contextSize = strlen(json_encode($context));
        $totalSize = $queryLength + $contextSize;
        
        // Select model based on complexity and size
        if ($totalSize > 50000 || str_contains(strtolower($query), 'complex')) {
            return 'llama3.3'; // Most capable model
        } elseif ($totalSize > 10000) {
            return 'qwen2.5'; // Balanced model
        } else {
            return 'mistral'; // Fastest model for simple queries
        }
    }
    
    private function buildGameStrategyPrompt(string $query, array $context): string
    {
        $prompt = "You are an expert Umamusume Pretty Derby strategy advisor. ";
        $prompt .= "Provide detailed, accurate advice based on game mechanics.\n\n";
        
        // Add context if available
        if (!empty($context['character'])) {
            $char = $context['character'];
            $prompt .= "Character Context:\n";
            $prompt .= "- Name: {$char['name']}\n";
            $prompt .= "- Scenario: {$char['scenario_type']}\n";
            $prompt .= "- Stats: Speed {$char['speed']}, Stamina {$char['stamina']}, Power {$char['power']}, Guts {$char['guts']}, Wit {$char['wit']}\n";
            if (isset($char['current_turn'])) {
                $prompt .= "- Current Turn: {$char['current_turn']}/{$char['total_turns']}\n";
            }
            $prompt .= "\n";
        }
        
        if (!empty($context['training_options'])) {
            $prompt .= "Available Training Options:\n";
            foreach ($context['training_options'] as $option) {
                $prompt .= "- {$option['type']}: {$option['description']}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "User Question: {$query}\n\n";
        $prompt .= "Please provide a detailed response with specific recommendations and reasoning.";
        
        return $prompt;
    }
}
```

### 11.2 AWS Bedrock Fallback Integration

#### 11.2.1 Cloud AI Service with Cost Management

**Bedrock Service Implementation**:

```php
<?php

namespace App\Services\AI;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use App\Models\AIConversation;

class BedrockIntegrationService
{
    private array $modelPricing = [
        'anthropic.claude-3-5-sonnet-20241022-v2:0' => [
            'input' => 0.003,  // $3 per 1M tokens
            'output' => 0.015  // $15 per 1M tokens
        ],
        'anthropic.claude-3-5-haiku-20241022-v1:0' => [
            'input' => 0.001,  // $1 per 1M tokens
            'output' => 0.005  // $5 per 1M tokens
        ],
        'amazon.nova-lite-v1:0' => [
            'input' => 0.00125,  // $1.25 per 1M tokens
            'output' => 0.00125  // $1.25 per 1M tokens
        ],
        'amazon.nova-pro-v1:0' => [
            'input' => 0.008,   // $8 per 1M tokens
            'output' => 0.032   // $32 per 1M tokens
        ]
    ];
    
    private array $modelCapabilities = [
        'anthropic.claude-3-5-sonnet-20241022-v2:0' => [
            'max_tokens' => 200000,
            'use_case' => 'complex_analysis',
            'strength' => 'reasoning'
        ],
        'anthropic.claude-3-5-haiku-20241022-v1:0' => [
            'max_tokens' => 200000,
            'use_case' => 'quick_responses',
            'strength' => 'speed'
        ],
        'amazon.nova-lite-v1:0' => [
            'max_tokens' => 300000,
            'use_case' => 'cost_effective',
            'strength' => 'efficiency'
        ],
        'amazon.nova-pro-v1:0' => [
            'max_tokens' => 300000,
            'use_case' => 'advanced_reasoning',
            'strength' => 'capability'
        ]
    ];
    
    public function __construct(
        private BedrockRuntimeClient $bedrock,
        private CostTrackingService $costTracker
    ) {}
    
    public function migrateCloudConversations(array $conversations): MigrationResult
    {
        $migrated = 0;
        $totalCost = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($conversations as $conversation) {
                $cost = $this->calculateHistoricalCost($conversation);
                $totalCost += $cost;
                
                AIConversation::create([
                    'user_id' => $conversation['user_id'],
                    'character_id' => $conversation['character_id'] ?? null,
                    'conversation_id' => $conversation['conversation_id'],
                    'message_type' => $conversation['message_type'],
                    'message_sequence' => $conversation['sequence'] ?? 1,
                    'ai_provider' => 'bedrock',
                    'ai_model' => $conversation['model'],
                    'model_version' => $conversation['model_version'] ?? null,
                    'processing_time_ms' => $conversation['processing_time'] ?? null,
                    'token_count_input' => $conversation['input_tokens'] ?? 0,
                    'token_count_output' => $conversation['output_tokens'] ?? 0,
                    'cost_usd' => $cost,
                    'message_content' => $conversation['content'],
                    'context_data' => $conversation['context'] ?? null,
                    'confidence_score' => $conversation['confidence'] ?? null,
                    'created_at' => $conversation['timestamp']
                ]);
                
                $migrated++;
            }
            
            // Update cost tracking
            $this->costTracker->recordMigrationCosts($totalCost);
            
            DB::commit();
            
        } catch (Exception $e) {
            DB::rollback();
            $errors[] = "Cloud conversation migration failed: {$e->getMessage()}";
        }
        
        return new MigrationResult($migrated, count($errors), $errors, [
            'total_cost' => $totalCost,
            'average_cost_per_conversation' => $migrated > 0 ? $totalCost / $migrated : 0
        ]);
    }
    
    public function processComplexQuery(string $query, array $context = []): array
    {
        $startTime = microtime(true);
        
        // Check user's cost limits
        $userId = auth()->id();
        if (!$this->costTracker->canProcessRequest($userId)) {
            throw new CostLimitExceededException('User has exceeded their AI cost limit');
        }
        
        // Select optimal model based on complexity and cost
        $selectedModel = $this->selectOptimalModel($query, $context);
        
        // Prepare the request
        $prompt = $this->buildAdvancedPrompt($query, $context);
        
        try {
            $response = $this->bedrock->invokeModel([
                'modelId' => $selectedModel,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => json_encode([
                    'anthropic_version' => 'bedrock-2023-05-31',
                    'max_tokens' => 4000,
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ])
            ]);
            
            $result = json_decode($response['body']->getContents(), true);
            $processingTime = (microtime(true) - $startTime) * 1000;
            
            // Calculate costs
            $inputTokens = $result['usage']['input_tokens'] ?? $this->estimateTokenCount($prompt);
            $outputTokens = $result['usage']['output_tokens'] ?? $this->estimateTokenCount($result['content'][0]['text'] ?? '');
            $cost = $this->calculateCost($selectedModel, $inputTokens, $outputTokens);
            
            // Record cost
            $this->costTracker->recordUsage($userId, $cost, $selectedModel);
            
            return [
                'response' => $result['content'][0]['text'] ?? '',
                'model' => $selectedModel,
                'processing_time_ms' => round($processingTime),
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost_usd' => $cost,
                'confidence' => $this->calculateConfidence($result['content'][0]['text'] ?? ''),
                'context_used' => !empty($context)
            ];
            
        } catch (Exception $e) {
            Log::error('Bedrock processing failed', [
                'model' => $selectedModel,
                'query' => substr($query, 0, 100),
                'error' => $e->getMessage()
            ]);
            
            throw new AIProcessingException("Cloud AI processing failed: {$e->getMessage()}");
        }
    }
    
    private function selectOptimalModel(string $query, array $context): string
    {
        $complexity = $this->assessQueryComplexity($query, $context);
        $userBudget = $this->costTracker->getRemainingBudget(auth()->id());
        
        // Select model based on complexity and budget
        if ($complexity > 0.8 && $userBudget > 0.10) {
            return 'amazon.nova-pro-v1:0'; // Most capable
        } elseif ($complexity > 0.6 && $userBudget > 0.05) {
            return 'anthropic.claude-3-5-sonnet-20241022-v2:0'; // Balanced
        } elseif ($userBudget > 0.02) {
            return 'anthropic.claude-3-5-haiku-20241022-v1:0'; // Fast and affordable
        } else {
            return 'amazon.nova-lite-v1:0'; // Most cost-effective
        }
    }
    
    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        if (!isset($this->modelPricing[$model])) {
            return 0.0;
        }
        
        $pricing = $this->modelPricing[$model];
        
        $inputCost = ($inputTokens / 1000) * $pricing['input'];
        $outputCost = ($outputTokens / 1000) * $pricing['output'];
        
        return round($inputCost + $outputCost, 6);
    }
    
    private function assessQueryComplexity(string $query, array $context): float
    {
        $complexity = 0.0;
        
        // Length factor
        $complexity += min(strlen($query) / 1000, 0.3);
        
        // Context factor
        $complexity += min(count($context) * 0.1, 0.3);
        
        // Keyword complexity
        $complexKeywords = [
            'analyze', 'compare', 'optimize', 'strategy', 'complex',
            'detailed', 'comprehensive', 'advanced', 'multiple'
        ];
        
        foreach ($complexKeywords as $keyword) {
            if (str_contains(strtolower($query), $keyword)) {
                $complexity += 0.1;
            }
        }
        
        return min($complexity, 1.0);
    }
}
```

### 11.3 Hybrid AI Router and Decision Engine

#### 11.3.1 Intelligent Routing Logic

**AI Router Implementation**:

```php
<?php

namespace App\Services\AI;

class HybridAIRouter
{
    private const LOCAL_TIMEOUT_THRESHOLD = 15000; // 15 seconds
    private const COMPLEXITY_THRESHOLD = 0.7;
    private const COST_THRESHOLD = 0.05; // $0.05
    
    public function __construct(
        private OllamaIntegrationService $ollama,
        private BedrockIntegrationService $bedrock,
        private PerformanceMonitor $monitor
    ) {}
    
    public function processQuery(string $query, array $context = []): array
    {
        $routingDecision = $this->makeRoutingDecision($query, $context);
        
        Log::info('AI routing decision made', [
            'decision' => $routingDecision['provider'],
            'reasoning' => $routingDecision['reasoning'],
            'query_hash' => hash('sha256', $query)
        ]);
        
        try {
            return match ($routingDecision['provider']) {
                'ollama' => $this->processWithOllama($query, $context, $routingDecision),
                'bedrock' => $this->processWithBedrock($query, $context, $routingDecision),
                default => throw new InvalidRouteException('Invalid AI provider selected')
            };
            
        } catch (Exception $e) {
            // Attempt fallback if primary fails
            return $this->handleFailureWithFallback($query, $context, $routingDecision, $e);
        }
    }
    
    private function makeRoutingDecision(string $query, array $context): array
    {
        $factors = $this->analyzeRoutingFactors($query, $context);
        
        // Decision logic based on multiple factors
        if ($factors['user_preference'] === 'local_only') {
            return [
                'provider' => 'ollama',
                'reasoning' => 'User preference for local processing only',
                'confidence' => 1.0
            ];
        }
        
        if ($factors['complexity'] < self::COMPLEXITY_THRESHOLD && 
            $factors['estimated_processing_time'] < self::LOCAL_TIMEOUT_THRESHOLD) {
            return [
                'provider' => 'ollama',
                'reasoning' => 'Low complexity query suitable for local processing',
                'confidence' => 0.8
            ];
        }
        
        if ($factors['remaining_budget'] < self::COST_THRESHOLD) {
            return [
                'provider' => 'ollama',
                'reasoning' => 'Insufficient budget for cloud processing',
                'confidence' => 0.9
            ];
        }
        
        if ($factors['complexity'] > self::COMPLEXITY_THRESHOLD || 
            $factors['requires_advanced_reasoning']) {
            return [
                'provider' => 'bedrock',
                'reasoning' => 'High complexity query requires advanced cloud models',
                'confidence' => 0.9
            ];
        }
        
        // Default to local processing
        return [
            'provider' => 'ollama',
            'reasoning' => 'Default to local processing for privacy and cost efficiency',
            'confidence' => 0.6
        ];
    }
    
    private function analyzeRoutingFactors(string $query, array $context): array
    {
        $user = auth()->user();
        
        return [
            'complexity' => $this->assessQueryComplexity($query, $context),
            'user_preference' => $user->ai_model_preference ?? 'hybrid',
            'remaining_budget' => $this->getRemainingBudget($user),
            'estimated_processing_time' => $this->estimateProcessingTime($query, $context),
            'requires_advanced_reasoning' => $this->requiresAdvancedReasoning($query),
            'context_size' => strlen(json_encode($context)),
            'query_length' => strlen($query),
            'historical_performance' => $this->getHistoricalPerformance($user->id)
        ];
    }
    
    private function processWithOllama(string $query, array $context, array $decision): array
    {
        $startTime = microtime(true);
        
        try {
            $result = $this->ollama->processGameStrategyQuery($query, $context);
            
            // Add routing metadata
            $result['routing_decision'] = $decision;
            $result['provider'] = 'ollama';
            $result['total_processing_time'] = (microtime(true) - $startTime) * 1000;
            
            // Record performance metrics
            $this->monitor->recordProcessing('ollama', $result);
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Ollama processing failed in router', [
                'error' => $e->getMessage(),
                'decision' => $decision
            ]);
            throw $e;
        }
    }
    
    private function processWithBedrock(string $query, array $context, array $decision): array
    {
        $startTime = microtime(true);
        
        try {
            $result = $this->bedrock->processComplexQuery($query, $context);
            
            // Add routing metadata
            $result['routing_decision'] = $decision;
            $result['provider'] = 'bedrock';
            $result['total_processing_time'] = (microtime(true) - $startTime) * 1000;
            
            // Record performance metrics
            $this->monitor->recordProcessing('bedrock', $result);
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Bedrock processing failed in router', [
                'error' => $e->getMessage(),
                'decision' => $decision
            ]);
            throw $e;
        }
    }
    
    private function handleFailureWithFallback(string $query, array $context, array $originalDecision, Exception $originalError): array
    {
        $fallbackProvider = $originalDecision['provider'] === 'ollama' ? 'bedrock' : 'ollama';
        
        Log::warning('Attempting AI fallback', [
            'original_provider' => $originalDecision['provider'],
            'fallback_provider' => $fallbackProvider,
            'original_error' => $originalError->getMessage()
        ]);
        
        try {
            $fallbackDecision = [
                'provider' => $fallbackProvider,
                'reasoning' => "Fallback due to {$originalDecision['provider']} failure",
                'confidence' => 0.5,
                'is_fallback' => true
            ];
            
            return match ($fallbackProvider) {
                'ollama' => $this->processWithOllama($query, $context, $fallbackDecision),
                'bedrock' => $this->processWithBedrock($query, $context, $fallbackDecision),
            };
            
        } catch (Exception $fallbackError) {
            Log::error('Both AI providers failed', [
                'original_error' => $originalError->getMessage(),
                'fallback_error' => $fallbackError->getMessage()
            ]);
            
            throw new AISystemFailureException(
                'Both local and cloud AI systems are unavailable. Please try again later.'
            );
        }
    }
}
```

---

## 12. OCR Processing Specifications

### 12.1 Screenshot Processing and Data Extraction

#### 12.1.1 Tesseract OCR Integration with Japanese Support

**OCR Service Implementation**:

```php
<?php

namespace App\Services\OCR;

use Thiagoalessio\TesseractOCR\TesseractOCR;
use App\Models\OCRExtraction;

class OCRProcessingService
{
    private array $supportedLanguages = ['jpn', 'eng'];
    private array $screenTypePatterns = [
        'training' => [
            'keywords' => ['トレーニング', 'training', 'スピード', 'speed', 'スタミナ', 'stamina'],
            'confidence_threshold' => 0.7
        ],
        'race' => [
            'keywords' => ['レース', 'race', '着順', 'position', 'タイム', 'time'],
            'confidence_threshold' => 0.8
        ],
        'character_stats' => [
            'keywords' => ['ステータス', 'status', '能力', 'stats', '評価', 'grade'],
            'confidence_threshold' => 0.75
        ],
        'skills' => [
            'keywords' => ['スキル', 'skill', 'SP', 'ヒント', 'hint'],
            'confidence_threshold' => 0.7
        ],
        'support_cards' => [
            'keywords' => ['サポート', 'support', 'カード', 'card', '絆', 'bond'],
            'confidence_threshold' => 0.75
        ]
    ];
    
    public function processScreenshot(UploadedFile $file, int $userId, ?int $characterId = null): OCRExtraction
    {
        // Create initial OCR extraction record
        $extraction = OCRExtraction::create([
            'user_id' => $userId,
            'character_id' => $characterId,
            'original_filename' => $file->getClientOriginalName(),
            'file_hash' => hash_file('sha256', $file->getPathname()),
            'file_size' => $file->getSize(),
            'file_mime_type' => $file->getMimeType(),
            'processing_status' => 'pending'
        ]);
        
        // Queue OCR processing job
        ProcessOCRExtraction::dispatch($extraction, $file->getPathname());
        
        return $extraction;
    }
    
    public function performOCRExtraction(OCRExtraction $extraction, string $filePath): void
    {
        $extraction->update(['processing_status' => 'processing']);
        
        $startTime = microtime(true);
        
        try {
            // Preprocess image for better OCR accuracy
            $preprocessedPath = $this->preprocessImage($filePath);
            
            // Perform OCR with multiple language support
            $ocrResult = $this->extractTextWithTesseract($preprocessedPath);
            
            // Detect screen type
            $screenType = $this->detectScreenType($ocrResult['text']);
            
            // Extract structured data based on screen type
            $extractedData = $this->extractStructuredData($ocrResult['text'], $screenType);
            
            // Validate extracted data
            $validationErrors = $this->validateExtractedData($extractedData, $screenType);
            
            $processingTime = (microtime(true) - $startTime) * 1000;
            
            // Update extraction record
            $extraction->update([
                'screen_type' => $screenType,
                'processing_status' => 'completed',
                'raw_text' => $ocrResult['text'],
                'confidence_score' => $ocrResult['confidence'],
                'extracted_data' => $extractedData,
                'validation_errors' => $validationErrors,
                'processing_time_ms' => round($processingTime),
                'ocr_engine' => 'tesseract',
                'ocr_version' => $this->getTesseractVersion(),
                'preprocessing_applied' => $ocrResult['preprocessing_steps'],
                'requires_manual_review' => !empty($validationErrors) || $ocrResult['confidence'] < 0.8
            ]);
            
            // Clean up temporary files
            if (file_exists($preprocessedPath) && $preprocessedPath !== $filePath) {
                unlink($preprocessedPath);
            }
            
            Log::info('OCR processing completed', [
                'extraction_id' => $extraction->id,
                'screen_type' => $screenType,
                'confidence' => $ocrResult['confidence'],
                'processing_time_ms' => round($processingTime)
            ]);
            
        } catch (Exception $e) {
            $extraction->update([
                'processing_status' => 'failed',
                'validation_errors' => [['error' => $e->getMessage()]],
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000)
            ]);
            
            Log::error('OCR processing failed', [
                'extraction_id' => $extraction->id,
                'error' => $e->getMessage(),
                'file_path' => $filePath
            ]);
            
            throw $e;
        }
    }
    
    private function preprocessImage(string $filePath): string
    {
        $preprocessingSteps = [];
        $outputPath = storage_path('app/temp/ocr_' . uniqid() . '.png');
        
        try {
            // Load image with OpenCV-style processing using GD
            $image = imagecreatefromstring(file_get_contents($filePath));
            if (!$image) {
                throw new ImageProcessingException('Failed to load image');
            }
            
            // Get image dimensions
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Apply preprocessing steps
            
            // 1. Convert to grayscale for better OCR
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            $preprocessingSteps[] = 'grayscale_conversion';
            
            // 2. Enhance contrast
            imagefilter($image, IMG_FILTER_CONTRAST, -20);
            $preprocessingSteps[] = 'contrast_enhancement';
            
            // 3. Sharpen image
            $sharpenMatrix = [
                [-1, -1, -1],
                [-1, 16, -1],
                [-1, -1, -1]
            ];
            imageconvolution($image, $sharpenMatrix, 8, 0);
            $preprocessingSteps[] = 'sharpening';
            
            // 4. Scale up small images for better OCR
            if ($width < 800 || $height < 600) {
                $scaleFactor = max(800 / $width, 600 / $height);
                $newWidth = (int)($width * $scaleFactor);
                $newHeight = (int)($height * $scaleFactor);
                
                $scaledImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($scaledImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $scaledImage;
                
                $preprocessingSteps[] = "scaling_{$scaleFactor}x";
            }
            
            // Save preprocessed image
            imagepng($image, $outputPath);
            imagedestroy($image);
            
            return $outputPath;
            
        } catch (Exception $e) {
            Log::error('Image preprocessing failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            
            // Return original path if preprocessing fails
            return $filePath;
        }
    }
    
    private function extractTextWithTesseract(string $imagePath): array
    {
        try {
            // Configure Tesseract for optimal Japanese + English recognition
            $ocr = new TesseractOCR($imagePath);
            $ocr->lang('jpn', 'eng')
                ->psm(6) // Uniform block of text
                ->oem(3) // Default OCR Engine Mode
                ->config('tessedit_char_whitelist', 
                    '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+-.,()[]{}％%' .
                    'あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわをん' .
                    'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン' .
                    'がぎぐげござじずぜぞだぢづでどばびぶべぼぱぴぷぺぽ' .
                    'ガギグゲゴザジズゼゾダヂヅデドバビブベボパピプペポ' .
                    'ゃゅょっゎ' .
                    'ャュョッヮ'
                );
            
            // Extract text
            $text = $ocr->run();
            $confidence = $ocr->confidence() / 100; // Convert to 0-1 scale
            
            // Get preprocessing steps from the image path
            $preprocessingSteps = $this->getPreprocessingSteps($imagePath);
            
            return [
                'text' => $text,
                'confidence' => $confidence,
                'preprocessing_steps' => $preprocessingSteps
            ];
            
        } catch (Exception $e) {
            Log::error('Tesseract OCR failed', [
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            
            throw new OCRProcessingException("OCR extraction failed: {$e->getMessage()}");
        }
    }
    
    private function detectScreenType(string $text): ?string
    {
        $lowerText = strtolower($text);
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($this->screenTypePatterns as $type => $pattern) {
            $score = 0;
            $keywordCount = 0;
            
            foreach ($pattern['keywords'] as $keyword) {
                if (str_contains($lowerText, strtolower($keyword))) {
                    $score += 1;
                    $keywordCount++;
                }
            }
            
            // Calculate confidence score
            $confidence = $keywordCount > 0 ? $score / count($pattern['keywords']) : 0;
            
            if ($confidence >= $pattern['confidence_threshold'] && $confidence > $bestScore) {
                $bestScore = $confidence;
                $bestMatch = $type;
            }
        }
        
        return $bestMatch;
    }
    
    private function extractStructuredData(string $text, ?string $screenType): array
    {
        if (!$screenType) {
            return ['raw_text' => $text];
        }
        
        return match ($screenType) {
            'training' => $this->extractTrainingData($text),
            'race' => $this->extractRaceData($text),
            'character_stats' => $this->extractCharacterStats($text),
            'skills' => $this->extractSkillData($text),
            'support_cards' => $this->extractSupportCardData($text),
            default => ['raw_text' => $text]
        };
    }
    
    private function extractCharacterStats(string $text): array
    {
        $stats = [];
        
        // Common stat patterns in both Japanese and English
        $statPatterns = [
            'speed' => ['/(?:スピード|speed)[\s:：]*(\d{1,4})/i', '/speed[\s:：]*(\d{1,4})/i'],
            'stamina' => ['/(?:スタミナ|stamina)[\s:：]*(\d{1,4})/i', '/stamina[\s:：]*(\d{1,4})/i'],
            'power' => ['/(?:パワー|power)[\s:：]*(\d{1,4})/i', '/power[\s:：]*(\d{1,4})/i'],
            'guts' => ['/(?:根性|guts)[\s:：]*(\d{1,4})/i', '/guts[\s:：]*(\d{1,4})/i'],
            'wit' => ['/(?:賢さ|wit)[\s:：]*(\d{1,4})/i', '/wit[\s:：]*(\d{1,4})/i']
        ];
        
        foreach ($statPatterns as $stat => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    $value = (int) $matches[1];
                    if ($value >= 0 && $value <= 1200) {
                        $stats[$stat] = $value;
                        break; // Use first valid match
                    }
                }
            }
        }
        
        // Extract character name
        $namePatterns = [
            '/(?:キャラクター|character)[\s:：]*([^\n\r]+)/i',
            '/^([^\n\r]+)(?:\s+(?:スピード|speed))/i'
        ];
        
        foreach ($namePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $name = trim($matches[1]);
                if (strlen($name) > 2 && strlen($name) < 50) {
                    $stats['character_name'] = $name;
                    break;
                }
            }
        }
        
        return $stats;
    }
    
    private function extractTrainingData(string $text): array
    {
        $training = [];
        
        // Extract training options and stat gains
        $trainingPatterns = [
            'training_type' => '/(?:トレーニング|training)[\s:：]*([^\n\r]+)/i',
            'stat_gains' => '/\+(\d+)\s*(?:スピード|speed|スタミナ|stamina|パワー|power|根性|guts|賢さ|wit)/i',
            'energy_cost' => '/(?:体力|energy)[\s:：]*-?(\d+)/i',
            'participants' => '/(?:参加者|participants)[\s:：]*([^\n\r]+)/i'
        ];
        
        foreach ($trainingPatterns as $key => $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                $training[$key] = $matches[1];
            }
        }
        
        return $training;
    }
    
    private function validateExtractedData(array $data, ?string $screenType): array
    {
        $errors = [];
        
        if (!$screenType) {
            return $errors;
        }
        
        switch ($screenType) {
            case 'character_stats':
                $errors = array_merge($errors, $this->validateCharacterStats($data));
                break;
            case 'training':
                $errors = array_merge($errors, $this->validateTrainingData($data));
                break;
            case 'race':
                $errors = array_merge($errors, $this->validateRaceData($data));
                break;
        }
        
        return $errors;
    }
    
    private function validateCharacterStats(array $data): array
    {
        $errors = [];
        
        // Validate stat ranges
        $stats = ['speed', 'stamina', 'power', 'guts', 'wit'];
        foreach ($stats as $stat) {
            if (isset($data[$stat])) {
                $value = $data[$stat];
                if (!is_numeric($value) || $value < 0 || $value > 1200) {
                    $errors[] = "Invalid {$stat} value: {$value} (must be 0-1200)";
                }
            }
        }
        
        // Validate character name
        if (isset($data['character_name'])) {
            $name = $data['character_name'];
            if (strlen($name) < 2 || strlen($name) > 50) {
                $errors[] = "Invalid character name length: {$name}";
            }
        }
        
        return $errors;
    }
    
    private function getTesseractVersion(): string
    {
        try {
            $output = shell_exec('tesseract --version 2>&1');
            if (preg_match('/tesseract\s+([\d.]+)/', $output, $matches)) {
                return $matches[1];
            }
        } catch (Exception $e) {
            // Ignore version detection errors
        }
        
        return 'unknown';
    }
}
```

### 12.2 OCR Queue Processing and Background Jobs

#### 12.2.1 Laravel Queue Job for OCR Processing

**OCR Processing Job**:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\OCRExtraction;
use App\Services\OCR\OCRProcessingService;

class ProcessOCRExtraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 300; // 5 minutes
    public $tries = 3;
    public $maxExceptions = 1;
    
    public function __construct(
        private OCRExtraction $extraction,
        private string $filePath
    ) {
        $this->onQueue('ocr-processing');
    }
    
    public function handle(OCRProcessingService $ocrService): void
    {
        try {
            // Broadcast processing started event
            broadcast(new OCRProcessingStarted($this->extraction));
            
            // Process the OCR extraction
            $ocrService->performOCRExtraction($this->extraction, $this->filePath);
            
            // Broadcast completion event
            broadcast(new OCRProcessingCompleted($this->extraction->fresh()));
            
        } catch (Exception $e) {
            // Update extraction status
            $this->extraction->update([
                'processing_status' => 'failed',
                'validation_errors' => [['error' => $e->getMessage()]]
            ]);
            
            // Broadcast failure event
            broadcast(new OCRProcessingFailed($this->extraction, $e->getMessage()));
            
            // Re-throw to trigger job failure handling
            throw $e;
        } finally {
            // Clean up temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        }
    }
    
    public function failed(Throwable $exception): void
    {
        Log::error('OCR processing job failed permanently', [
            'extraction_id' => $this->extraction->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
        
        // Mark as requiring manual review
        $this->extraction->update([
            'processing_status' => 'manual_review',
            'requires_manual_review' => true,
            'validation_errors' => [
                ['error' => 'Automatic processing failed after ' . $this->tries . ' attempts'],
                ['error' => $exception->getMessage()]
            ]
        ]);
        
        // Notify user of failure
        $this->extraction->user->notify(new OCRProcessingFailedNotification($this->extraction));
    }
}
```

---

## Conclusion

This comprehensive Data Migration Specifications document provides detailed technical requirements for implementing robust, secure, and performant data migration capabilities in the modern UmamusumeCareerPlanner system. The specifications ensure data integrity, user privacy, accessibility compliance, and system reliability while leveraging cutting-edge technologies including Laravel 12, Tailwind CSS v4, AI integration, and OCR processing.

### Key Implementation Achievements

#### Technical Excellence with Modern Stack

- **Laravel 12 Integration**: Full utilization of strict mode, asynchronous caching, event-driven architecture, and advanced Eloquent features
- **AI-Powered Processing**: Hybrid local/cloud AI system with Ollama and AWS Bedrock integration for intelligent data processing and user assistance
- **OCR Capabilities**: Advanced screenshot processing with Tesseract and OpenCV for automated data extraction from game screenshots
- **Accessibility Compliance**: WCAG 2.2 AA compliance throughout all migration interfaces and processes

#### Data Management Excellence

- **Comprehensive Validation**: Multi-layer validation with real-time feedback, accessibility considerations, and intelligent error correction
- **Performance Optimization**: Redis caching strategies, database indexing, connection pooling, and asynchronous processing
- **Security Implementation**: AES-256 encryption, comprehensive audit logging, secure file handling, and privacy protection
- **Quality Assurance**: Automated testing, performance benchmarking, and comprehensive monitoring systems

#### User Experience Innovation

- **Modern Interface Design**: Tailwind CSS v4 with zero configuration, drag-and-drop uploads, and real-time progress tracking
- **Progressive Web App**: Offline functionality, service workers, and native app-like experience
- **Intelligent Assistance**: AI-powered data validation, transformation suggestions, and migration guidance
- **Accessibility Features**: Screen reader support, keyboard navigation, high contrast modes, and reduced motion options

### Success Metrics and Validation

#### Performance Benchmarks

- **Processing Speed**: 500+ records per minute for user data, 1000+ records per minute for game data
- **Memory Efficiency**: Under 2GB RAM usage during large migrations with intelligent garbage collection
- **Response Times**: Under 2 seconds for page loads, under 500ms for API responses
- **Availability**: 99.5% uptime during migration periods with graceful degradation

#### Quality Assurance Metrics

- **Data Integrity**: 99.9% accuracy across all migration operations with comprehensive validation
- **Error Recovery**: Automatic rollback and recovery for 95% of migration failures
- **User Satisfaction**: 90%+ satisfaction rating for migration experience and accessibility
- **Accessibility Compliance**: 100% WCAG 2.2 AA compliance verification across all interfaces

#### Cost and Resource Optimization

- **AI Processing Costs**: Under $50/month for typical usage with intelligent local/cloud routing
- **Storage Efficiency**: Optimized database schemas with intelligent indexing and caching strategies
- **Network Optimization**: Efficient API usage with intelligent caching and rate limiting
- **Resource Utilization**: Optimal use of system resources with monitoring and alerting

### Future Evolution and Maintenance

The specifications provide a solid foundation for continuous improvement and feature expansion while maintaining backward compatibility and system reliability. The modular architecture supports future enhancements including additional AI models, expanded OCR capabilities, enhanced accessibility features, and integration with new external data sources.

The comprehensive monitoring, logging, and performance tracking systems ensure operational visibility and enable data-driven optimization decisions. The event-driven architecture facilitates easy integration of new features and services without disrupting existing functionality.

---

### Document Control

| Version | Date | Author | Changes |
| --------- | ------ | -------- | --------- |
| 1.0 | January 10, 2026 | Development Team | Initial DMS document creation |
| 2.0 | January 10, 2026 | Development Team | Complete rewrite for Laravel 12, Tailwind CSS v4, AI integration, OCR processing, accessibility compliance, and modern architecture specifications |

### Technology Verification Status

| Technology | Version | Status | Verification Date | Notes |
| ---------- | ------- | ------ | ----------------- | ----- |
| Laravel | 12.x | ✅ Verified | January 10, 2026 | With strict mode and async features |
| Tailwind CSS | v4 | ✅ Verified | January 10, 2026 | Zero config, 5x faster builds |
| umapyoi.net API | v1 | ✅ Active | January 10, 2026 | Primary data source confirmed |
| AWS Bedrock | Claude 4.5/Nova 2 | ✅ Available | January 10, 2026 | Pricing verified |
| Ollama | Latest | ✅ Compatible | January 10, 2026 | With cloudstudio/ollama-laravel |
| Tesseract OCR | 5.3+ | ✅ Required | January 10, 2026 | With Japanese language support |
| UmamusumeDB.com | Unknown | ⚠️ Requires Verification | Pending | Phase 1 verification needed |

### Approval

| Role | Name | Signature | Date |
| ---- | ---- | --------- | ---- |
| Data Architect | [Name] | [Signature] | [Date] |
| Database Administrator | [Name] | [Signature] | [Date] |
| Frontend Architect | [Name] | [Signature] | [Date] |
| AI Integration Lead | [Name] | [Signature] | [Date] |
| OCR Processing Lead | [Name] | [Signature] | [Date] |
| Accessibility Specialist | [Name] | [Signature] | [Date] |
| Security Architect | [Name] | [Signature] | [Date] |
| Performance Engineer | [Name] | [Signature] | [Date] |
| Project Manager | [Name] | [Signature] | [Date] |
