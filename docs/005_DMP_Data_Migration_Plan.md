# Data Migration Plan (DMP)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.0  
**Date**: January 10, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Updated**: Aligned with Laravel 12, Tailwind CSS v4, and modern architecture specifications

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Migration Overview](#2-migration-overview)
3. [Data Sources and Targets](#3-data-sources-and-targets)
4. [Migration Strategy](#4-migration-strategy)
5. [Data Mapping and Transformation](#5-data-mapping-and-transformation)
6. [Migration Procedures](#6-migration-procedures)
7. [Testing and Validation](#7-testing-and-validation)
8. [Risk Management](#8-risk-management)
9. [Rollback Procedures](#9-rollback-procedures)
10. [AI Integration and External APIs](#11-ai-integration-and-external-apis)
11. [Performance and Monitoring](#12-performance-and-monitoring)

---

## 1. Introduction

### 1.1 Purpose

This Data Migration Plan (DMP) outlines the comprehensive strategy for migrating data into the Umamusume Career Planner system built with Laravel 12 and modern web technologies. The plan addresses:

- **Initial System Population**: Game data from verified external APIs (umapyoi.net, UmamusumeDB.com)
- **User Data Import**: Personal tracking data from Google Docs, spreadsheets, and other formats
- **AI Integration Data**: Conversation history, OCR extractions, and machine learning datasets
- **Community Data Integration**: Meta tier lists, strategy guides, and community contributions
- **Ongoing Synchronization**: Real-time updates from external sources with intelligent caching
- **Schema Evolution**: Database migrations for system upgrades and feature additions

### 1.2 Scope

The migration plan covers all aspects of the modern UmamusumeCareerPlanner system:

#### Core Data Migration

- **Game Reference Data**: Characters (100+ with aptitudes), skills (500+ with evolution chains), support cards (300+ with meta tiers), races (200+ with requirements)
- **User Personal Data**: Character builds, career progression, training logs, race results, skill acquisitions
- **System Configuration**: Application settings, user preferences, caching configurations

#### Advanced Features Migration

- **AI System Data**: Ollama local model conversations, AWS Bedrock fallback usage, hybrid AI routing decisions
- **OCR Processing Data**: Screenshot extractions, validation results, confidence scoring, error corrections
- **External API Cache**: umapyoi.net responses, UmamusumeDB.com data, community tier lists with TTL management
- **Performance Analytics**: Training prediction accuracy, recommendation effectiveness, user interaction patterns

#### Technology-Specific Requirements

- **Laravel 12 Features**: Database migrations with strict mode, Eloquent relationships, queue jobs, event-driven architecture
- **Redis Integration**: Caching strategies, session management, queue processing, real-time data synchronization
- **Modern Frontend**: Progressive Web App data, service worker caches, offline functionality, accessibility compliance

### 1.3 Migration Principles

#### Data Integrity and Security

- **Zero Data Loss**: Comprehensive validation and rollback mechanisms for all migration operations
- **Privacy First**: Local-first architecture with encrypted storage for sensitive user data
- **WCAG 2.2 AA Compliance**: Accessible data formats and migration interfaces for all users
- **Audit Trail**: Complete logging of all migration activities with retention policies

#### Performance and Scalability

- **Efficient Processing**: Batch operations with configurable chunk sizes (default 100 records)
- **Resource Optimization**: Memory usage under 2GB, processing rates above 500 records/minute
- **Concurrent Support**: Up to 10 simultaneous user imports with queue management
- **Caching Strategy**: Redis-based caching with intelligent invalidation and warming

#### Modern Architecture Alignment

- **Service-Oriented Design**: Repository pattern, service layer abstraction, dependency injection
- **Event-Driven Processing**: Laravel events and listeners for decoupled migration workflows
- **API-First Approach**: RESTful endpoints for all migration operations with comprehensive error handling
- **Cloud-Ready Structure**: Abstraction layers supporting future cloud deployment while maintaining local-first operation

---

## 2. Migration Overview

### 2.1 Migration Types and Modern Architecture

#### 2.1.1 Initial System Population (Laravel 12 + External APIs)

**Purpose**: Populate new Laravel 12 system with essential game data from verified sources
**Technology Stack**: Laravel 12 migrations, Redis caching, external API integration
**Data Volume**: ~100MB of game reference data with relationships
**Complexity**: High - requires API integration, data transformation, and relationship mapping

**Key Components**:

- **Database Migrations**: Laravel 12 migration system with proper foreign key constraints
- **API Integration**: umapyoi.net (verified active), UmamusumeDB.com (requires verification)
- **Caching Layer**: Redis-based caching with TTL management and intelligent invalidation
- **Data Validation**: Comprehensive validation rules for game data integrity

#### 2.1.2 User Data Import with Modern UI

**Purpose**: Import existing user tracking data through accessible, modern interface
**Technology Stack**: Tailwind CSS v4, Progressive Web App features, drag-and-drop uploads
**Data Volume**: Variable (1-500MB per user, supporting large datasets)
**Complexity**: High - multiple source formats, validation, accessibility compliance

**Key Features**:

- **Accessible Interface**: WCAG 2.2 AA compliant upload interface with keyboard navigation
- **Format Support**: CSV, XLSX, JSON, Google Sheets API integration
- **Real-time Validation**: Client-side and server-side validation with immediate feedback
- **Progress Tracking**: WebSocket-based progress updates with detailed status information

#### 2.1.3 AI Integration Data Migration

**Purpose**: Migrate and synchronize AI-related data for hybrid local/cloud system
**Technology Stack**: Ollama local models, AWS Bedrock integration, conversation management
**Data Volume**: Variable conversation history and OCR processing results
**Complexity**: Medium - structured data with context preservation requirements

**Components**:

- **Conversation History**: Ollama and Bedrock chat logs with context preservation
- **OCR Extractions**: Screenshot processing results with confidence scoring
- **Model Performance**: Usage statistics, cost tracking, accuracy metrics
- **Hybrid Routing**: Decision logs for local vs cloud processing choices

#### 2.1.4 Incremental Updates with Event-Driven Architecture

**Purpose**: Sync with external data sources using Laravel 12 event system
**Technology Stack**: Laravel Events/Listeners, Queue jobs, Redis pub/sub
**Data Volume**: 1-50MB per update cycle with change detection
**Complexity**: Medium - established transformation pipelines with event handling

**Event-Driven Features**:

- **Change Detection**: Automated monitoring of external API changes
- **Queue Processing**: Background jobs for data synchronization with Laravel Horizon
- **Event Broadcasting**: Real-time updates to connected clients via WebSockets
- **Conflict Resolution**: Intelligent merging of conflicting data from multiple sources

#### 2.1.5 Schema Evolution with Laravel 12

**Purpose**: Database structure changes during system evolution
**Technology Stack**: Laravel 12 migrations, database versioning, rollback capabilities
**Data Volume**: Full database (potentially GB scale with user data)
**Complexity**: High - requires careful planning, testing, and zero-downtime deployment

**Modern Migration Features**:

- **Strict Mode Compliance**: Laravel 12 strict mode for preventing N+1 queries
- **Relationship Integrity**: Comprehensive foreign key constraints and cascade rules
- **Performance Optimization**: Index management and query optimization during migrations
- **Rollback Safety**: Comprehensive rollback procedures with data integrity verification

### 2.2 Migration Timeline (Updated for Modern Stack)

#### Phase 1: Foundation Setup (Week 1-2)

##### Laravel 12 + Redis + External API Integration

- **Week 1**: Laravel 12 project initialization with XAMPP configuration
  - Database setup with MySQL 8.0+ and proper indexing strategy
  - Redis installation via WSL with connection pooling
  - External API client development for umapyoi.net integration
  - Initial migration files creation with comprehensive relationships

- **Week 2**: Core data population and validation
  - Game data extraction from verified APIs with rate limiting
  - Data transformation pipeline implementation
  - Validation rules implementation with comprehensive error handling
  - Initial database seeding with reference data

#### Phase 2: Modern User Interface (Week 3-4)

##### Tailwind CSS v4 + PWA + Accessibility

- **Week 3**: Frontend foundation with modern technologies
  - Tailwind CSS v4 setup with zero configuration and 5x faster builds
  - Progressive Web App implementation with service workers
  - Accessible upload interface with WCAG 2.2 AA compliance
  - Drag-and-drop functionality with keyboard navigation support

- **Week 4**: User import tools with real-time features
  - File format parsing (CSV, XLSX, JSON) with validation
  - Real-time progress tracking with WebSocket integration
  - Error handling and user feedback systems
  - Preview and confirmation workflows with accessibility features

#### Phase 3: AI Integration (Week 5-6)

##### Ollama + AWS Bedrock + Hybrid Processing

- **Week 5**: Local AI setup and integration
  - Ollama installation and model management (Llama 3.3, Mistral, Qwen)
  - cloudstudio/ollama-laravel package integration
  - Conversation management and context preservation
  - Performance monitoring and optimization

- **Week 6**: Cloud AI fallback and hybrid routing
  - AWS Bedrock integration with verified models (Claude 4.5, Nova 2)
  - Intelligent routing based on complexity and cost
  - Cost tracking and budget management
  - Hybrid system testing and optimization

#### Phase 4: Advanced Features (Week 7-8)

##### OCR + Community Integration + Performance Optimization

- **Week 7**: OCR and screenshot processing
  - Tesseract OCR setup with Japanese language support
  - OpenCV integration for image preprocessing
  - Screenshot upload and processing workflows
  - Data extraction and validation systems

- **Week 8**: Performance optimization and monitoring
  - Database query optimization and indexing
  - Redis caching strategy implementation
  - Performance monitoring with APM integration
  - Load testing and capacity planning

### 2.3 Technology Integration Points

#### 2.3.1 Laravel 12 Specific Features

**Migration System Enhancements**:

- **Strict Mode**: Prevents N+1 queries and lazy loading issues
- **Attribute Casting**: Modern JSON and enum casting for complex data
- **Relationship Optimization**: Eager loading strategies and query optimization
- **Event System**: Comprehensive event-driven architecture for migration workflows

**Performance Features**:

- **Asynchronous Caching**: Background cache operations without blocking user interactions
- **Connection Pooling**: Optimized database connections for concurrent operations
- **Queue Optimization**: Laravel Horizon for queue monitoring and optimization
- **Memory Management**: Efficient memory usage patterns for large dataset processing

#### 2.3.2 Modern Frontend Integration

**Tailwind CSS v4 Benefits**:

- **Zero Configuration**: Automatic setup with 5x faster build times
- **Modern CSS Features**: Container queries, CSS Grid, advanced selectors
- **Performance Optimization**: Automatic purging and optimization
- **Accessibility Built-in**: WCAG compliance features integrated by default

**Progressive Web App Features**:

- **Offline Functionality**: Service worker caching for migration interfaces
- **Background Sync**: Automatic data synchronization when connectivity restored
- **Push Notifications**: Migration completion and error notifications
- **Installable Experience**: Native app-like experience for frequent users

#### 2.3.3 AI and External Service Integration

**Hybrid AI Architecture**:

- **Local Processing**: Ollama models for privacy-sensitive operations
- **Cloud Fallback**: AWS Bedrock for complex analysis requiring advanced models
- **Cost Optimization**: Intelligent routing to minimize cloud usage costs
- **Performance Monitoring**: Real-time tracking of processing times and accuracy

**External API Management**:

- **Rate Limiting**: Respect API limits with exponential backoff strategies
- **Caching Strategy**: Redis-based caching with intelligent TTL management
- **Fallback Mechanisms**: Graceful degradation when external services unavailable
- **Data Quality**: Validation and scoring of external data sources

---

## 3. Data Sources and Targets

### 3.1 External Data Sources (Updated for 2026)

#### 3.1.1 Primary Game Data APIs

**umapyoi.net API** ✅ **VERIFIED ACTIVE**

- **Base URL**: `https://api.umapyoi.net/api/v1/`
- **Data Types**: Characters (100+ with full aptitude matrices), support cards (300+ with meta tiers), news updates, event information
- **Format**: JSON REST API with comprehensive schemas
- **Update Frequency**: Daily for news/events, weekly for meta updates
- **Reliability**: High (verified active as of January 2026)
- **Rate Limits**: 100 requests/minute, 1000 requests/hour per IP
- **Authentication**: None required (public API)
- **Caching Strategy**: Redis-based with 24-hour TTL for static data, 1-hour TTL for dynamic content

**Response Format Example**:

```json
{
  "data": [
    {
      "id": 1001,
      "name": "Special Week",
      "name_en": "Special Week",
      "rarity": 3,
      "aptitudes": {
        "turf": "A", "dirt": "G",
        "sprint": "G", "mile": "A", "medium": "A", "long": "B",
        "front_runner": "A", "pace_chaser": "B", "late_surger": "A", "end_closer": "C"
      },
      "growth_rates": {
        "speed": 20, "stamina": 10, "power": 15, "guts": 10, "wit": 15
      },
      "updated_at": "2026-01-10T10:00:00Z"
    }
  ],
  "meta": {
    "total": 150,
    "per_page": 50,
    "current_page": 1
  }
}
```

**UmamusumeDB.com** ⚠️ **REQUIRES VERIFICATION**

- **Data Types**: Training calculations, meta analysis, community tier lists
- **Format**: Web scraping or API (TBD during implementation)
- **Update Frequency**: Weekly for meta analysis, monthly for tier lists
- **Reliability**: Unknown (requires verification during Phase 1)
- **Rate Limits**: Unknown (to be determined)
- **Fallback Strategy**: Manual data entry and community sourcing if unavailable

#### 3.1.2 Community Data Sources with Modern Integration

**Meta Tier Lists** (Community-Driven)

- **Primary Sources**:
  - Discord servers (GameWith, Umamusume Community)
  - Reddit communities (r/UmaMusume)
  - Japanese wikis and strategy sites
- **Format**: JSON export from community databases
- **Update Frequency**: Monthly or on significant meta shifts
- **Validation Process**: Community consensus verification with voting systems
- **Integration Method**: API endpoints for community-maintained databases
- **Quality Control**: Multi-source validation and expert review processes

##### Strategy Guides and Documentation

- **Sources**: Community wikis, GitHub repositories, strategy databases
- **Format**: Markdown with structured metadata and YAML frontmatter
- **Update Frequency**: As needed based on game updates and meta changes
- **Integration**: Reference links, summary extraction, and searchable content
- **Version Control**: Git-based versioning for strategy guide updates

### 3.2 User Data Sources (Enhanced for Modern Workflows)

#### 3.2.1 Google Workspace Integration

##### Google Sheets API Integration

- **Authentication**: OAuth 2.0 with secure token management
- **Supported Operations**: Read-only access to user-authorized spreadsheets
- **Data Format**: Real-time API access with automatic schema detection
- **Rate Limits**: 100 requests/100 seconds per user
- **Privacy**: User consent required, no data stored without permission

**Common Google Sheets Structure**:

```text
Sheet 1: Characters
| Character Name | Scenario | Speed | Stamina | Power | Guts | Wit | Final Grade | Notes |
|----------------|----------|-------|---------|-------|------|-----|-------------|-------|
| Special Week   | URA      | 800   | 650     | 750   | 400  | 600 | A+          | First |

Sheet 2: Training Log
| Character | Turn | Training Type | Stat Gains | Energy Cost | Participants | Spirit Burst |
|-----------|------|---------------|------------|-------------|--------------|--------------|
| Special   | 1    | Speed         | +15 Speed  | 20          | Kitasan      | No           |

Sheet 3: Race Results
| Character | Race Name | Position | Performance | Strategy | Weather | Notes |
|-----------|-----------|----------|-------------|----------|---------|-------|
| Special   | Japan Cup | 1st      | Excellent   | Late     | Good    | Perfect |
```

**Data Validation and Transformation**:

- **Character Name Mapping**: Automatic matching with game database
- **Stat Range Validation**: 0-1200 range with boundary checking
- **Grade Format Standardization**: G+ through SS with consistent formatting
- **Missing Data Handling**: Intelligent defaults and user confirmation prompts

#### 3.2.2 File Upload System with Modern UI

**Supported File Formats**:

- **CSV/TSV**: Comma and tab-separated values with encoding detection
- **Microsoft Excel**: .xlsx and .xls with multiple sheet support
- **JSON**: Structured data with schema validation
- **Plain Text**: Intelligent parsing of formatted text data

**Upload Interface Features** (Tailwind CSS v4 + Accessibility):

- **Drag-and-Drop**: Visual feedback with accessibility announcements
- **File Validation**: Real-time MIME type and size validation (max 50MB)
- **Progress Tracking**: WebSocket-based progress updates with screen reader support
- **Preview Mode**: Data preview before import with correction interface
- **Batch Processing**: Multiple file upload with queue management

**Security and Privacy**:

- **File Scanning**: Virus scanning and malicious content detection
- **Temporary Storage**: Encrypted temporary files with automatic cleanup
- **User Consent**: Clear privacy notices and data handling policies
- **Access Control**: User authentication required for all upload operations

### 3.3 Target Database Schema (Laravel 12 Optimized)

#### 3.3.1 Core Tables with Modern Laravel Features

**Users Table** (Laravel 12 + Sanctum)

```sql
CREATE TABLE ucp_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    
    -- User preferences
    theme ENUM('light', 'dark', 'auto') DEFAULT 'auto',
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    
    -- Privacy settings
    data_sharing_consent BOOLEAN DEFAULT FALSE,
    analytics_consent BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
);
```

**Characters Table** (Enhanced with Modern Features)

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
    
    -- Goals and targets (JSON casting in Laravel)
    target_stats JSON NULL,
    race_objectives JSON NULL,
    
    -- Metadata
    final_grade VARCHAR(10) NULL,
    completion_date TIMESTAMP NULL,
    notes TEXT,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_user_scenario (user_id, scenario_type),
    INDEX idx_name (name),
    INDEX idx_created_at (created_at),
    INDEX idx_completion (completion_date),
    
    -- Foreign keys with cascade
    FOREIGN KEY (user_id) REFERENCES ucp_users(id) ON DELETE CASCADE,
    
    -- Check constraints for data integrity
    CONSTRAINT chk_speed_range CHECK (speed >= 0 AND speed <= 1200),
    CONSTRAINT chk_stamina_range CHECK (stamina >= 0 AND stamina <= 1200),
    CONSTRAINT chk_power_range CHECK (power >= 0 AND power <= 1200),
    CONSTRAINT chk_guts_range CHECK (guts >= 0 AND guts <= 1200),
    CONSTRAINT chk_wit_range CHECK (wit >= 0 AND wit <= 1200),
    CONSTRAINT chk_energy_range CHECK (energy_level >= 0 AND energy_level <= 100)
);
```

#### 3.3.2 Advanced Tables for Modern Features

**AI Conversations Table** (Hybrid AI System)

```sql
CREATE TABLE ucp_ai_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    character_id BIGINT UNSIGNED NULL,
    
    -- Conversation metadata
    conversation_id VARCHAR(255) NOT NULL,
    message_type ENUM('user', 'assistant') NOT NULL,
    
    -- AI system information
    ai_model ENUM('ollama_llama', 'ollama_mistral', 'bedrock_claude', 'bedrock_nova') NOT NULL,
    model_version VARCHAR(50) NULL,
    processing_time_ms INT UNSIGNED NULL,
    token_count INT UNSIGNED NULL,
    cost_usd DECIMAL(10, 6) NULL DEFAULT 0,
    
    -- Message content
    message_content TEXT NOT NULL,
    context_data JSON NULL,
    confidence_score DECIMAL(3, 2) NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_user_conversation (user_id, conversation_id),
    INDEX idx_character (character_id),
    INDEX idx_ai_model (ai_model),
    INDEX idx_created_at (created_at),
    
    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES ucp_users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES ucp_characters(id) ON DELETE SET NULL
);
```

**OCR Extractions Table** (Screenshot Processing)

```sql
CREATE TABLE ucp_ocr_extractions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    character_id BIGINT UNSIGNED NULL,
    
    -- File information
    original_filename VARCHAR(255) NOT NULL,
    file_hash VARCHAR(64) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    
    -- OCR processing
    screen_type ENUM('training', 'race', 'character_stats', 'skills', 'support_cards') NULL,
    processing_status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    confidence_score DECIMAL(3, 2) NULL,
    
    -- Extracted data
    extracted_data JSON NULL,
    validation_errors JSON NULL,
    manual_corrections JSON NULL,
    
    -- Processing metadata
    processing_time_ms INT UNSIGNED NULL,
    ocr_engine VARCHAR(50) DEFAULT 'tesseract',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_user_character (user_id, character_id),
    INDEX idx_file_hash (file_hash),
    INDEX idx_screen_type (screen_type),
    INDEX idx_status (processing_status),
    INDEX idx_created_at (created_at),
    
    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES ucp_users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES ucp_characters(id) ON DELETE SET NULL
);
```

**External Data Cache Table** (API Response Caching)

```sql
CREATE TABLE ucp_external_data_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Cache key information
    cache_key VARCHAR(255) NOT NULL UNIQUE,
    data_source ENUM('umapyoi', 'umamusumedb', 'community') NOT NULL,
    data_type ENUM('characters', 'skills', 'support_cards', 'races', 'meta_tiers') NOT NULL,
    
    -- Cache data
    cached_data JSON NOT NULL,
    data_hash VARCHAR(64) NOT NULL,
    
    -- Cache metadata
    ttl_seconds INT UNSIGNED NOT NULL DEFAULT 86400,
    hit_count INT UNSIGNED DEFAULT 0,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    -- Indexes
    INDEX idx_cache_key (cache_key),
    INDEX idx_data_source_type (data_source, data_type),
    INDEX idx_expires_at (expires_at),
    INDEX idx_last_accessed (last_accessed),
    
    -- Check constraint
    CONSTRAINT chk_ttl_positive CHECK (ttl_seconds > 0)
);
```

---

## 4. Migration Strategy

### 4.1 Migration Approach (Laravel 12 + Modern Architecture)

#### 4.1.1 Phased Migration with Event-Driven Architecture

**Selected Approach**: Phased Migration with Laravel 12 Event System

**Rationale**:

- **Incremental Risk Reduction**: Each phase can be validated independently
- **Event-Driven Decoupling**: Laravel Events and Listeners enable modular processing
- **Real-time Feedback**: WebSocket integration provides immediate user feedback
- **Rollback Granularity**: Individual components can be rolled back without affecting others
- **Performance Optimization**: Asynchronous processing with Laravel Queues

**Phase Structure with Modern Technologies**:

1. **Foundation Phase**: Laravel 12 setup, database migrations, Redis configuration
2. **API Integration Phase**: External data sources with intelligent caching
3. **User Interface Phase**: Tailwind CSS v4, PWA features, accessibility compliance
4. **AI Integration Phase**: Ollama local models, AWS Bedrock fallback
5. **Advanced Features Phase**: OCR processing, community integration, analytics

#### 4.1.2 Data Migration Patterns (Enhanced)

##### Extract, Transform, Load (ETL) with Laravel Services

```php
<?php

namespace App\Services\Migration;

class ModernETLService
{
    public function __construct(
        private DataExtractor $extractor,
        private DataTransformer $transformer,
        private DataLoader $loader,
        private ValidationService $validator,
        private CacheManager $cache,
        private EventDispatcher $events
    ) {}
    
    public function migrate(MigrationRequest $request): MigrationResult
    {
        // Dispatch migration started event
        $this->events->dispatch(new MigrationStarted($request));
        
        try {
            // Extract with caching
            $extracted = $this->extractor->extract($request->getSource());
            $this->cache->remember("migration.{$request->getId()}.extracted", $extracted);
            
            // Transform with validation
            $transformed = $this->transformer->transform($extracted);
            $validation = $this->validator->validate($transformed);
            
            if (!$validation->isValid()) {
                throw new MigrationException('Validation failed', $validation->getErrors());
            }
            
            // Load with transaction safety
            $result = DB::transaction(function () use ($transformed) {
                return $this->loader->load($transformed);
            });
            
            // Dispatch success event
            $this->events->dispatch(new MigrationCompleted($result));
            
            return $result;
            
        } catch (Exception $e) {
            $this->events->dispatch(new MigrationFailed($request, $e));
            throw $e;
        }
    }
}
```

##### Change Data Capture (CDC) with Laravel Events

```php
<?php

namespace App\Listeners;

class ExternalDataChangeListener
{
    public function handle(ExternalDataChanged $event): void
    {
        // Queue background job for data synchronization
        ProcessExternalDataChange::dispatch($event->getSource(), $event->getChanges())
            ->onQueue('data-sync')
            ->delay(now()->addMinutes(5)); // Batch changes
    }
}

class ProcessExternalDataChange implements ShouldQueue
{
    public function handle(string $source, array $changes): void
    {
        // Intelligent change processing
        foreach ($changes as $change) {
            match ($change['type']) {
                'character_update' => $this->updateCharacterData($change),
                'skill_addition' => $this->addNewSkill($change),
                'meta_tier_change' => $this->updateMetaTiers($change),
                default => Log::warning("Unknown change type: {$change['type']}")
            };
        }
        
        // Invalidate relevant caches
        Cache::tags(['external-data', $source])->flush();
        
        // Broadcast updates to connected clients
        broadcast(new ExternalDataUpdated($source, $changes));
    }
}
```

### 4.2 Migration Tools and Technologies (Updated Stack)

#### 4.2.1 Laravel 12 Migration Framework (Enhanced)

##### Database Migrations with Modern Features

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharactersTableWithModernFeatures extends Migration
{
    public function up(): void
    {
        Schema::create('ucp_characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('ucp_users')->cascadeOnDelete();
            
            // Character data with JSON casting
            $table->string('name');
            $table->enum('scenario_type', ['ura_finale', 'unity_cup']);
            $table->json('stats')->comment('Speed, Stamina, Power, Guts, Wit');
            $table->json('aptitudes')->comment('Distance, Surface, Style aptitudes');
            $table->json('goals')->nullable()->comment('Target stats and objectives');
            
            // State tracking
            $table->tinyInteger('energy_level')->default(100);
            $table->enum('mood_status', ['awful', 'bad', 'normal', 'good', 'great'])->default('normal');
            
            // Metadata
            $table->string('final_grade', 10)->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Performance indexes
            $table->index(['user_id', 'scenario_type']);
            $table->index('name');
            $table->index('created_at');
            $table->index('completion_date');
            
            // Check constraints for data integrity
            $table->check('json_valid(stats)');
            $table->check('json_valid(aptitudes)');
            $table->check('energy_level >= 0 AND energy_level <= 100');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('ucp_characters');
    }
}
```

##### Artisan Commands with Progress Tracking

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Migration\GameDataMigrationService;

class MigrateGameDataCommand extends Command
{
    protected $signature = 'migrate:game-data 
                           {--source=umapyoi : Data source to use}
                           {--force : Force migration even if data exists}
                           {--dry-run : Show what would be migrated without executing}
                           {--chunk=100 : Number of records to process per batch}
                           {--timeout=1800 : Maximum execution time in seconds}';
    
    protected $description = 'Migrate game data from external sources with modern features';
    
    public function handle(GameDataMigrationService $service): int
    {
        $this->info('🚀 Starting game data migration with Laravel 12...');
        
        $options = [
            'source' => $this->option('source'),
            'force' => $this->option('force'),
            'dry_run' => $this->option('dry-run'),
            'chunk_size' => (int) $this->option('chunk'),
            'timeout' => (int) $this->option('timeout'),
        ];
        
        try {
            // Create progress bar
            $progressBar = $this->output->createProgressBar();
            $progressBar->setFormat('verbose');
            
            // Execute migration with real-time progress
            $result = $service->migrate($options, function ($progress) use ($progressBar) {
                $progressBar->setProgress($progress['current']);
                $progressBar->setMaxSteps($progress['total']);
                $this->line(" Processing: {$progress['current']}/{$progress['total']} - {$progress['message']}");
            });
            
            $progressBar->finish();
            $this->newLine(2);
            
            // Display results
            $this->info('✅ Migration completed successfully!');
            $this->table(
                ['Entity', 'Records', 'Status', 'Processing Time'],
                [
                    ['Characters', $result->getCharacterCount(), '✅ Success', $result->getCharacterTime()],
                    ['Skills', $result->getSkillCount(), '✅ Success', $result->getSkillTime()],
                    ['Support Cards', $result->getSupportCardCount(), '✅ Success', $result->getSupportCardTime()],
                ]
            );
            
            return Command::SUCCESS;
            
        } catch (Exception $e) {
            $this->error("❌ Migration failed: {$e->getMessage()}");
            $this->line("Stack trace available in logs: storage/logs/laravel.log");
            return Command::FAILURE;
        }
    }
}
```

#### 4.2.2 External Integration Tools (Enhanced)

##### HTTP Clients with Advanced Features

```php
<?php

namespace App\Services\External;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UmapyoiApiClient
{
    private const BASE_URL = 'https://api.umapyoi.net/api/v1/';
    private const RATE_LIMIT = 100; // requests per minute
    private const CACHE_TTL = 3600; // 1 hour
    
    public function __construct(
        private HttpFactory $http,
        private RateLimiter $rateLimiter
    ) {}
    
    public function getCharacters(array $options = []): Collection
    {
        $cacheKey = 'umapyoi.characters.' . md5(serialize($options));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($options) {
            // Check rate limit
            if (!$this->rateLimiter->attempt('umapyoi-api', self::RATE_LIMIT, 60)) {
                throw new RateLimitExceededException('Rate limit exceeded for umapyoi.net API');
            }
            
            $response = $this->http
                ->timeout(30)
                ->retry(3, 1000) // 3 retries with 1 second delay
                ->withHeaders([
                    'User-Agent' => 'UmamusumeCareerPlanner/2.0',
                    'Accept' => 'application/json',
                ])
                ->get(self::BASE_URL . 'characters', $options);
            
            if (!$response->successful()) {
                Log::error('umapyoi.net API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'options' => $options
                ]);
                
                throw new ExternalApiException(
                    "API request failed with status {$response->status()}"
                );
            }
            
            $data = $response->json();
            
            // Validate response structure
            if (!isset($data['data']) || !is_array($data['data'])) {
                throw new InvalidApiResponseException('Invalid response structure from umapyoi.net');
            }
            
            return collect($data['data'])->map(function ($character) {
                return $this->transformCharacterData($character);
            });
        });
    }
    
    private function transformCharacterData(array $data): array
    {
        return [
            'external_id' => $data['id'],
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'rarity' => $data['rarity'],
            'aptitudes' => $data['aptitudes'],
            'growth_rates' => $data['growth_rates'] ?? [],
            'updated_at' => $data['updated_at'] ?? now(),
        ];
    }
}
```

##### File Processing with Modern Features

```php
<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use League\Csv\Reader;

class ModernFileProcessor
{
    public function processFile(UploadedFile $file, array $options = []): ProcessedFileResult
    {
        // Validate file security
        $this->validateFileSecurity($file);
        
        // Determine file type and processor
        $processor = match ($file->getClientOriginalExtension()) {
            'csv' => new CsvProcessor(),
            'xlsx', 'xls' => new ExcelProcessor(),
            'json' => new JsonProcessor(),
            default => throw new UnsupportedFileTypeException("Unsupported file type: {$file->getClientOriginalExtension()}")
        };
        
        // Process with progress tracking
        return $processor->process($file, $options, function ($progress) {
            // Broadcast progress to user via WebSocket
            broadcast(new FileProcessingProgress(
                auth()->id(),
                $progress['current'],
                $progress['total'],
                $progress['message']
            ));
        });
    }
    
    private function validateFileSecurity(UploadedFile $file): void
    {
        // File size validation
        if ($file->getSize() > 50 * 1024 * 1024) { // 50MB
            throw new FileTooLargeException('File size exceeds 50MB limit');
        }
        
        // MIME type validation
        $allowedMimes = [
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/json'
        ];
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new InvalidFileTypeException("Invalid MIME type: {$file->getMimeType()}");
        }
        
        // Scan for malicious content
        $this->scanForMaliciousContent($file);
    }
    
    private function scanForMaliciousContent(UploadedFile $file): void
    {
        $handle = fopen($file->getPathname(), 'rb');
        $header = fread($handle, 1024);
        fclose($handle);
        
        $maliciousPatterns = [
            '<?php', '<script', 'javascript:', 'vbscript:', 'data:text/html'
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (stripos($header, $pattern) !== false) {
                throw new MaliciousFileException("Potentially malicious content detected: {$pattern}");
            }
        }
    }
}
```

### 4.3 Performance and Scalability Considerations

#### 4.3.1 Laravel 12 Performance Features

##### Asynchronous Caching

```php
<?php

// Background cache warming without blocking user requests
Cache::async()->remember('game-data.characters', 3600, function () {
    return $this->fetchCharactersFromAPI();
});

// Asynchronous cache invalidation
Cache::async()->forget(['game-data.characters', 'game-data.skills']);
```

##### Connection Pooling and Query Optimization

```php
<?php

// Optimized database queries with Laravel 12 strict mode
Character::with(['aptitudes', 'skills.hints', 'careers.races'])
    ->whereHas('user', fn($q) => $q->where('id', auth()->id()))
    ->when($scenario, fn($q) => $q->where('scenario_type', $scenario))
    ->orderBy('created_at', 'desc')
    ->paginate(20);

// Batch operations for large datasets
Character::upsert($characters, ['external_id'], ['name', 'aptitudes', 'updated_at']);
```

#### 4.3.2 Redis Integration Strategy

##### Intelligent Caching Layers

```php
<?php

namespace App\Services\Cache;

class IntelligentCacheManager
{
    // L1: Application cache (fast, small)
    // L2: Redis cache (medium speed, larger)
    // L3: Database (slow, persistent)
    
    public function getCharacterData(int $characterId): array
    {
        return Cache::tags(['characters', "character.{$characterId}"])
            ->remember("character.{$characterId}", 300, function () use ($characterId) {
                return Character::with('aptitudes', 'skills')->find($characterId)->toArray();
            });
    }
    
    public function invalidateCharacterCache(int $characterId): void
    {
        Cache::tags(["character.{$characterId}"])->flush();
        
        // Warm cache asynchronously
        Cache::async()->remember("character.{$characterId}", 300, function () use ($characterId) {
            return Character::with('aptitudes', 'skills')->find($characterId)->toArray();
        });
    }
}
```

---

## 5. Data Mapping and Transformation

### 5.1 Game Data Mapping

#### 5.1.1 Character Data Transformation

**Source Format (umapyoi.net)**:

```json
{
  "id": 1001,
  "name": "Special Week",
  "rarity": 3,
  "aptitudes": {
    "turf": "A",
    "dirt": "G",
    "sprint": "G",
    "mile": "A",
    "medium": "A",
    "long": "B"
  }
}
```

**Target Format (Local Database)**:

```sql
INSERT INTO game_characters (
  external_id, name, rarity, 
  turf_aptitude, dirt_aptitude,
  sprint_aptitude, mile_aptitude, 
  medium_aptitude, long_aptitude,
  created_at, updated_at
) VALUES (
  1001, 'Special Week', 3,
  'A', 'G', 'G', 'A', 'A', 'B',
  NOW(), NOW()
);
```

**Transformation Rules**:

- Map external IDs to internal references
- Convert aptitude objects to individual columns
- Add metadata fields (created_at, updated_at)
- Validate aptitude grades (G through SS)

#### 5.1.2 Skill Data Transformation

**Source Challenges**:

- Multiple sources with different formats
- Inconsistent naming conventions
- Missing or incomplete information
- Language barriers (Japanese sources)

**Transformation Process**:

1. **Normalization**: Standardize skill names and descriptions
2. **Categorization**: Assign skill types and categories
3. **Cost Calculation**: Determine SP costs and hint discounts
4. **Evolution Mapping**: Link Normal skills to Rare counterparts
5. **Validation**: Verify data completeness and accuracy

### 5.2 User Data Mapping

#### 5.2.1 Character Import Mapping

**Common User Data Format**:

```csv
Character Name,Scenario,Speed,Stamina,Power,Guts,Wit,Final Grade
Special Week,URA Finale,800,650,750,400,600,A+
Silence Suzuka,Unity Cup,900,500,700,350,650,A
```

**Target Database Structure**:

```sql
INSERT INTO characters (
  user_id, name, scenario_type,
  speed, stamina, power, guts, wit,
  final_grade, created_at, updated_at
) VALUES (
  1, 'Special Week', 'ura_finale',
  800, 650, 750, 400, 600,
  'A+', NOW(), NOW()
);
```

**Transformation Challenges**:

- Inconsistent character naming
- Missing or invalid stat values
- Unknown scenario types
- Grade format variations

**Validation Rules**:

- Character names must match game database
- Stats must be within valid ranges (0-1200)
- Scenario types must be 'ura_finale' or 'unity_cup'
- Grades must follow standard format (G+ through SS)

---

## 6. Migration Procedures

### 6.1 Pre-Migration Activities

#### 6.1.1 Environment Preparation

**Database Setup**:

- Create migration-specific database user with limited permissions
- Set up separate migration database for testing
- Configure connection pooling for concurrent operations
- Enable query logging for audit trail

**Backup Procedures**:

- Full database backup before any migration
- Incremental backups during long-running migrations
- File system backups for uploaded user data
- Configuration backups for rollback scenarios

#### 6.1.2 Data Source Validation

**External API Testing**:

- Verify API availability and response formats
- Test rate limiting and error handling
- Validate data completeness and accuracy
- Establish baseline metrics for comparison

**User Data Analysis**:

- Analyze uploaded files for format consistency
- Identify common data quality issues
- Estimate processing time and resource requirements
- Prepare user communication for data issues

### 6.2 Migration Execution

#### 6.2.1 Game Data Migration

##### Step 1: API Data Extraction

```php
// Extract character data from umapyoi.net
$characters = $this->apiClient->getCharacters();
$this->validateApiResponse($characters);
$this->logMigrationStep('characters_extracted', count($characters));
```

##### Step 2: Data Transformation

```php
// Transform API data to database format
foreach ($characters as $character) {
    $transformed = $this->transformCharacterData($character);
    $this->validateTransformedData($transformed);
    $this->queueForInsertion($transformed);
}
```

##### Step 3: Database Population

```php
// Batch insert with error handling
DB::transaction(function () use ($transformedData) {
    foreach (array_chunk($transformedData, 100) as $batch) {
        GameCharacter::insert($batch);
        $this->logProgress(count($batch));
    }
});
```

#### 6.2.2 User Data Import

##### Step 1: File Upload and Validation

```php
// Process uploaded user file
$file = $request->file('user_data');
$this->validateFileFormat($file);
$data = $this->parseUserFile($file);
$this->validateUserData($data);
```

##### Step 2: Data Mapping and Transformation

```php
// Map user data to system format
$mappedData = [];
foreach ($data as $row) {
    $character = $this->mapUserCharacter($row);
    if ($character) {
        $mappedData[] = $character;
    } else {
        $this->logDataIssue($row, 'mapping_failed');
    }
}
```

##### Step 3: User Confirmation and Import

```php
// Present mapped data for user confirmation
$this->presentImportPreview($mappedData);
if ($user->confirmsImport()) {
    $this->importUserData($mappedData);
    $this->notifyImportComplete();
}
```

### 6.3 Post-Migration Validation

#### 6.3.1 Data Integrity Checks

**Record Count Validation**:

- Compare source and target record counts
- Verify no data loss during transformation
- Check for duplicate records
- Validate foreign key relationships

**Data Quality Validation**:

- Verify stat ranges and grade formats
- Check character name consistency
- Validate aptitude combinations
- Confirm skill evolution relationships

#### 6.3.2 Functional Testing

**Application Testing**:

- Test character creation and editing
- Verify training predictions work correctly
- Check skill management functionality
- Validate race preparation features

**Performance Testing**:

- Measure query response times
- Test concurrent user operations
- Validate caching effectiveness
- Check memory usage patterns

---

## 7. Testing and Validation

### 7.1 Testing Strategy

#### 7.1.1 Unit Testing

**Migration Component Tests**:

- Data transformation functions
- Validation rule implementations
- Error handling mechanisms
- Rollback procedures

**Test Coverage Requirements**:

- 90%+ coverage for migration utilities
- 100% coverage for data validation
- Comprehensive error scenario testing
- Performance benchmark validation

#### 7.1.2 Integration Testing

**End-to-End Migration Tests**:

- Complete game data migration cycle
- User data import workflows
- Incremental update processes
- Schema migration procedures

**External Integration Tests**:

- API connectivity and data retrieval
- Rate limiting and error handling
- Fallback mechanism validation
- Data consistency verification

### 7.2 Validation Procedures

#### 7.2.1 Automated Validation

**Data Consistency Checks**:

```php
// Validate character data consistency
$this->assertCharacterStatsValid();
$this->assertAptitudeGradesValid();
$this->assertSkillEvolutionValid();
$this->assertForeignKeyIntegrity();
```

**Performance Validation**:

```php
// Verify migration performance metrics
$this->assertMigrationTimeWithinLimits();
$this->assertMemoryUsageAcceptable();
$this->assertDatabasePerformance();
```

#### 7.2.2 Manual Validation

**User Acceptance Testing**:

- Import sample user data and verify accuracy
- Test application functionality with migrated data
- Validate user interface displays correctly
- Confirm all features work as expected

**Data Quality Review**:

- Manual spot-checking of migrated records
- Comparison with source data for accuracy
- Validation of complex transformations
- Review of error logs and resolution

---

## 8. Risk Management

### 8.1 Migration Risks

#### 8.1.1 Data Loss Risks

**Risk**: Incomplete or corrupted data migration
**Probability**: Medium
**Impact**: High
**Mitigation**:

- Comprehensive backup procedures
- Transaction-based migrations with rollback
- Incremental validation checkpoints
- Multiple data source verification

**Risk**: User data privacy breach during import
**Probability**: Low
**Impact**: High
**Mitigation**:

- Encrypted file uploads and processing
- Secure temporary storage with automatic cleanup
- Access logging and audit trails
- User consent and data handling policies

#### 8.1.2 System Performance Risks

**Risk**: Migration process impacts system performance
**Probability**: Medium
**Impact**: Medium
**Mitigation**:

- Off-peak migration scheduling
- Resource monitoring and throttling
- Batch processing with progress tracking
- Separate migration database for testing

**Risk**: External API rate limiting or failures
**Probability**: High
**Impact**: Medium
**Mitigation**:

- Respect API rate limits with backoff strategies
- Implement retry mechanisms with exponential backoff
- Cache responses to reduce API dependency
- Fallback to manual data entry if needed

### 8.2 Contingency Planning

#### 8.2.1 Rollback Procedures

**Database Rollback**:

- Restore from pre-migration backup
- Revert schema changes using down migrations
- Clear cached data and reset application state
- Notify users of rollback and data status

**Partial Rollback**:

- Identify and isolate problematic data
- Rollback specific migration steps
- Preserve successfully migrated data
- Continue with corrected migration process

#### 8.2.2 Alternative Approaches

**Manual Data Entry**:

- Provide forms for manual game data entry
- Create templates for user data input
- Implement validation and assistance tools
- Gradual migration as external sources become available

**Community Sourcing**:

- Leverage community contributions for missing data
- Implement crowd-sourced validation mechanisms
- Create incentives for data quality contributions
- Establish community moderation processes

---

## 9. Rollback Procedures

### 9.1 Rollback Triggers

#### 9.1.1 Automatic Rollback Conditions

**Data Integrity Failures**:

- Foreign key constraint violations
- Data validation failures exceeding threshold (>5%)
- Critical application functionality failures
- Database corruption or inconsistency

**Performance Degradation**:

- Migration time exceeding 4x estimated duration
- System resource exhaustion (>90% memory/CPU)
- Database deadlocks or blocking issues
- User-facing service disruption

#### 9.1.2 Manual Rollback Decisions

**Data Quality Issues**:

- Significant data accuracy problems discovered
- User reports of missing or incorrect data
- External API data inconsistencies
- Community feedback indicating problems

**Business Requirements**:

- Stakeholder decision to halt migration
- Legal or compliance concerns
- Resource constraints or priority changes
- External dependency failures

### 9.2 Rollback Execution

#### 9.2.1 Immediate Actions

**Stop Migration Process**:

```php
// Halt all migration jobs and processes
Queue::clear('migration');
$this->stopMigrationWorkers();
$this->lockMigrationTables();
```

**Assess Current State**:

```php
// Determine migration progress and data state
$progress = $this->getMigrationProgress();
$dataIntegrity = $this->checkDataIntegrity();
$this->logRollbackInitiation($progress, $dataIntegrity);
```

#### 9.2.2 Data Restoration

**Database Restoration**:

```php
// Restore from backup with validation
$this->validateBackupIntegrity();
$this->restoreDatabase($backupFile);
$this->verifyRestorationSuccess();
$this->updateSystemStatus('rollback_complete');
```

**File System Cleanup**:

```php
// Clean up temporary files and caches
$this->clearMigrationTempFiles();
$this->invalidateMigrationCaches();
$this->resetFilePermissions();
```

### 9.3 Post-Rollback Activities

#### 9.3.1 System Verification

**Functionality Testing**:

- Verify all application features work correctly
- Test database queries and performance
- Validate user authentication and authorization
- Confirm external integrations function properly

**Data Consistency Checks**:

- Verify pre-migration data integrity
- Check for any residual migration artifacts
- Validate backup restoration completeness
- Confirm system configuration consistency

#### 9.3.2 Communication and Documentation

**User Communication**:

- Notify users of rollback completion
- Explain impact on their data and usage
- Provide timeline for migration retry
- Offer alternative solutions if available

**Documentation Updates**:

- Document rollback reasons and process
- Update migration procedures based on lessons learned
- Revise risk assessments and mitigation strategies
- Prepare improved migration plan for retry

---

## 11. AI Integration and External APIs

### 11.1 Hybrid AI System Migration

#### 11.1.1 Ollama Local AI Integration

##### Local Model Setup and Data Migration

```php
<?php

namespace App\Services\AI;

use Cloudstudio\OllamaLaravel\Facades\OllamaLaravel;

class OllamaIntegrationService
{
    private array $supportedModels = [
        'llama3.3' => ['size' => '70B', 'context' => 128000, 'use_case' => 'general'],
        'mistral' => ['size' => '7B', 'context' => 32000, 'use_case' => 'fast_responses'],
        'qwen2.5' => ['size' => '14B', 'context' => 32000, 'use_case' => 'multilingual']
    ];
    
    public function migrateConversationHistory(array $conversations): MigrationResult
    {
        $migrated = 0;
        $errors = [];
        
        foreach ($conversations as $conversation) {
            try {
                // Validate conversation format
                $this->validateConversationFormat($conversation);
                
                // Transform legacy format to new structure
                $transformed = $this->transformConversationData($conversation);
                
                // Store with proper relationships
                AIConversation::create([
                    'user_id' => $transformed['user_id'],
                    'character_id' => $transformed['character_id'],
                    'conversation_id' => $transformed['conversation_id'],
                    'message_type' => $transformed['message_type'],
                    'ai_model' => 'ollama_' . $transformed['model'],
                    'message_content' => $transformed['content'],
                    'context_data' => $transformed['context'],
                    'processing_time_ms' => $transformed['processing_time'],
                    'created_at' => $transformed['timestamp']
                ]);
                
                $migrated++;
                
            } catch (Exception $e) {
                $errors[] = "Conversation {$conversation['id']}: {$e->getMessage()}";
            }
        }
        
        return new MigrationResult($migrated, count($errors), $errors);
    }
    
    public function setupModelConfiguration(): void
    {
        foreach ($this->supportedModels as $model => $config) {
            // Verify model availability
            if ($this->isModelAvailable($model)) {
                // Configure model settings
                $this->configureModel($model, $config);
                Log::info("Ollama model configured: {$model}");
            } else {
                Log::warning("Ollama model not available: {$model}");
            }
        }
    }
    
    private function isModelAvailable(string $model): bool
    {
        try {
            $response = OllamaLaravel::agent()
                ->model($model)
                ->ask('Test connection');
            
            return !empty($response);
        } catch (Exception $e) {
            return false;
        }
    }
}
```

#### 11.1.2 AWS Bedrock Fallback Integration

##### Cloud AI Service Migration and Cost Tracking

```php
<?php

namespace App\Services\AI;

use Aws\BedrockRuntime\BedrockRuntimeClient;

class BedrockIntegrationService
{
    private array $modelPricing = [
        'anthropic.claude-3-5-sonnet-20241022-v2:0' => ['input' => 0.003, 'output' => 0.015],
        'anthropic.claude-3-5-haiku-20241022-v1:0' => ['input' => 0.001, 'output' => 0.005],
        'amazon.nova-lite-v1:0' => ['input' => 0.00125, 'output' => 0.00125],
        'amazon.nova-pro-v1:0' => ['input' => 0.008, 'output' => 0.032]
    ];
    
    public function __construct(
        private BedrockRuntimeClient $bedrock,
        private CostTrackingService $costTracker
    ) {}
    
    public function migrateCloudConversations(array $conversations): MigrationResult
    {
        $migrated = 0;
        $totalCost = 0;
        
        foreach ($conversations as $conversation) {
            // Calculate historical costs
            $cost = $this->calculateConversationCost($conversation);
            $totalCost += $cost;
            
            // Store with cost tracking
            AIConversation::create([
                'user_id' => $conversation['user_id'],
                'conversation_id' => $conversation['conversation_id'],
                'message_type' => $conversation['message_type'],
                'ai_model' => 'bedrock_' . $conversation['model'],
                'model_version' => $conversation['model_version'],
                'message_content' => $conversation['content'],
                'token_count' => $conversation['token_count'],
                'cost_usd' => $cost,
                'processing_time_ms' => $conversation['processing_time'],
                'created_at' => $conversation['timestamp']
            ]);
            
            $migrated++;
        }
        
        // Update cost tracking
        $this->costTracker->recordMigrationCosts($totalCost);
        
        return new MigrationResult($migrated, 0, [], ['total_cost' => $totalCost]);
    }
    
    private function calculateConversationCost(array $conversation): float
    {
        $model = $conversation['model'];
        $tokenCount = $conversation['token_count'] ?? 0;
        
        if (!isset($this->modelPricing[$model])) {
            return 0.0;
        }
        
        $pricing = $this->modelPricing[$model];
        
        // Estimate input/output split (typically 70/30)
        $inputTokens = $tokenCount * 0.7;
        $outputTokens = $tokenCount * 0.3;
        
        return ($inputTokens * $pricing['input'] / 1000) + 
               ($outputTokens * $pricing['output'] / 1000);
    }
}
```

### 11.2 OCR System Data Migration

#### 11.2.1 Screenshot Processing Migration

##### OCR Extraction Data Migration

```php
<?php

namespace App\Services\OCR;

use Thiagoalessio\TesseractOCR\TesseractOCR;

class OCRMigrationService
{
    public function migrateScreenshotExtractions(array $extractions): MigrationResult
    {
        $migrated = 0;
        $errors = [];
        
        foreach ($extractions as $extraction) {
            try {
                // Validate screenshot file
                if (!$this->validateScreenshotFile($extraction['file_path'])) {
                    throw new InvalidFileException("Invalid screenshot file: {$extraction['file_path']}");
                }
                
                // Re-process with current OCR engine for consistency
                $ocrResult = $this->reprocessScreenshot($extraction['file_path']);
                
                // Store migration result
                OCRExtraction::create([
                    'user_id' => $extraction['user_id'],
                    'character_id' => $extraction['character_id'],
                    'original_filename' => basename($extraction['file_path']),
                    'file_hash' => hash_file('sha256', $extraction['file_path']),
                    'file_size' => filesize($extraction['file_path']),
                    'screen_type' => $this->detectScreenType($ocrResult),
                    'processing_status' => 'completed',
                    'confidence_score' => $ocrResult['confidence'],
                    'extracted_data' => $ocrResult['data'],
                    'validation_errors' => $ocrResult['errors'],
                    'processing_time_ms' => $ocrResult['processing_time'],
                    'ocr_engine' => 'tesseract_5.3',
                    'created_at' => $extraction['original_date']
                ]);
                
                $migrated++;
                
            } catch (Exception $e) {
                $errors[] = "Screenshot {$extraction['file_path']}: {$e->getMessage()}";
            }
        }
        
        return new MigrationResult($migrated, count($errors), $errors);
    }
    
    private function reprocessScreenshot(string $filePath): array
    {
        $startTime = microtime(true);
        
        try {
            // Configure Tesseract for Japanese + English
            $ocr = new TesseractOCR($filePath);
            $ocr->lang('jpn', 'eng')
                ->psm(6) // Uniform block of text
                ->oem(3) // Default OCR Engine Mode
                ->config('tessedit_char_whitelist', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+-.,()[]{}スペシャルウィークサイレンススズカトウカイテイオー');
            
            $text = $ocr->run();
            $confidence = $ocr->confidence();
            
            // Parse extracted text based on screen type
            $parsedData = $this->parseExtractedText($text);
            $validationErrors = $this->validateExtractedData($parsedData);
            
            return [
                'text' => $text,
                'confidence' => $confidence,
                'data' => $parsedData,
                'errors' => $validationErrors,
                'processing_time' => (microtime(true) - $startTime) * 1000
            ];
            
        } catch (Exception $e) {
            return [
                'text' => '',
                'confidence' => 0,
                'data' => [],
                'errors' => [$e->getMessage()],
                'processing_time' => (microtime(true) - $startTime) * 1000
            ];
        }
    }
    
    private function detectScreenType(array $ocrResult): ?string
    {
        $text = strtolower($ocrResult['text']);
        
        return match (true) {
            str_contains($text, 'training') || str_contains($text, 'トレーニング') => 'training',
            str_contains($text, 'race') || str_contains($text, 'レース') => 'race',
            str_contains($text, 'skill') || str_contains($text, 'スキル') => 'skills',
            str_contains($text, 'support') || str_contains($text, 'サポート') => 'support_cards',
            preg_match('/\d{3,4}/', $text) => 'character_stats', // Likely contains stat numbers
            default => null
        };
    }
}
```

### 11.3 External API Data Synchronization

#### 11.3.1 Real-time Data Sync Migration

##### External Data Cache Migration

```php
<?php

namespace App\Services\External;

class ExternalDataSyncService
{
    public function migrateExternalDataCache(array $cacheEntries): MigrationResult
    {
        $migrated = 0;
        $errors = [];
        
        foreach ($cacheEntries as $entry) {
            try {
                // Validate cache entry structure
                $this->validateCacheEntry($entry);
                
                // Transform to new cache format
                $transformed = $this->transformCacheEntry($entry);
                
                // Store in new external data cache table
                ExternalDataCache::create([
                    'cache_key' => $transformed['key'],
                    'data_source' => $transformed['source'],
                    'data_type' => $transformed['type'],
                    'cached_data' => $transformed['data'],
                    'data_hash' => hash('sha256', json_encode($transformed['data'])),
                    'ttl_seconds' => $transformed['ttl'],
                    'created_at' => $transformed['cached_at'],
                    'expires_at' => $transformed['expires_at']
                ]);
                
                $migrated++;
                
            } catch (Exception $e) {
                $errors[] = "Cache entry {$entry['key']}: {$e->getMessage()}";
            }
        }
        
        // Set up automated sync jobs
        $this->setupAutomatedSync();
        
        return new MigrationResult($migrated, count($errors), $errors);
    }
    
    private function setupAutomatedSync(): void
    {
        // Schedule daily sync for character data
        Schedule::command('sync:external-data characters')
            ->daily()
            ->at('02:00')
            ->withoutOverlapping()
            ->runInBackground();
        
        // Schedule weekly sync for meta tier lists
        Schedule::command('sync:external-data meta-tiers')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping()
            ->runInBackground();
        
        // Schedule hourly sync for news and events
        Schedule::command('sync:external-data news')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
    }
}
```

---

## 12. Performance and Monitoring

### 12.1 Performance Migration Specifications

#### 12.1.1 Database Performance Migration

##### Index Migration and Optimization

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class OptimizeDatabasePerformance extends Migration
{
    public function up(): void
    {
        // Add composite indexes for common query patterns
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->index(['user_id', 'scenario_type', 'created_at'], 'idx_user_scenario_date');
            $table->index(['scenario_type', 'final_grade'], 'idx_scenario_grade');
            $table->index(['completion_date'], 'idx_completion_date');
        });
        
        Schema::table('ucp_ai_conversations', function (Blueprint $table) {
            $table->index(['user_id', 'conversation_id', 'created_at'], 'idx_user_conversation_date');
            $table->index(['ai_model', 'created_at'], 'idx_model_date');
            $table->index(['character_id', 'created_at'], 'idx_character_date');
        });
        
        Schema::table('ucp_ocr_extractions', function (Blueprint $table) {
            $table->index(['user_id', 'screen_type', 'processing_status'], 'idx_user_screen_status');
            $table->index(['file_hash'], 'idx_file_hash');
            $table->index(['processing_status', 'created_at'], 'idx_status_date');
        });
        
        // Add full-text search indexes
        DB::statement('ALTER TABLE ucp_characters ADD FULLTEXT(name, notes)');
        DB::statement('ALTER TABLE ucp_ai_conversations ADD FULLTEXT(message_content)');
        
        // Optimize table storage
        DB::statement('OPTIMIZE TABLE ucp_characters, ucp_ai_conversations, ucp_ocr_extractions');
    }
    
    public function down(): void
    {
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->dropIndex('idx_user_scenario_date');
            $table->dropIndex('idx_scenario_grade');
            $table->dropIndex('idx_completion_date');
        });
        
        // Remove other indexes...
    }
}
```

#### 12.1.2 Redis Performance Configuration

##### Cache Performance Migration

```php
<?php

namespace App\Services\Performance;

class RedisCacheMigrationService
{
    public function migrateToOptimizedCaching(): void
    {
        // Configure Redis for optimal performance
        $this->configureRedisSettings();
        
        // Migrate existing cache data to new structure
        $this->migrateCacheStructure();
        
        // Set up cache warming strategies
        $this->setupCacheWarming();
        
        // Configure cache monitoring
        $this->setupCacheMonitoring();
    }
    
    private function configureRedisSettings(): void
    {
        // Configure Redis for memory optimization
        Redis::config('set', 'maxmemory-policy', 'allkeys-lru');
        Redis::config('set', 'maxmemory', '2gb');
        
        // Enable compression for large values
        Redis::config('set', 'compression', 'yes');
        
        // Configure persistence for important data
        Redis::config('set', 'save', '900 1 300 10 60 10000');
    }
    
    private function migrateCacheStructure(): void
    {
        // Migrate to hierarchical cache keys
        $oldKeys = Redis::keys('*');
        
        foreach ($oldKeys as $oldKey) {
            $newKey = $this->transformCacheKey($oldKey);
            $value = Redis::get($oldKey);
            $ttl = Redis::ttl($oldKey);
            
            // Set with new key structure
            if ($ttl > 0) {
                Redis::setex($newKey, $ttl, $value);
            } else {
                Redis::set($newKey, $value);
            }
            
            // Remove old key
            Redis::del($oldKey);
        }
    }
    
    private function setupCacheWarming(): void
    {
        // Warm frequently accessed data
        $this->warmGameDataCache();
        $this->warmUserDataCache();
        $this->warmAIModelCache();
    }
    
    private function warmGameDataCache(): void
    {
        // Pre-load character data
        Cache::remember('game-data:characters:all', 3600, function () {
            return GameCharacter::with('aptitudes')->get();
        });
        
        // Pre-load skill data
        Cache::remember('game-data:skills:all', 3600, function () {
            return GameSkill::with('evolution')->get();
        });
        
        // Pre-load support card data
        Cache::remember('game-data:support-cards:all', 3600, function () {
            return GameSupportCard::with('effects')->get();
        });
    }
}
```

### 12.2 Monitoring and Alerting Migration

#### 12.2.1 Performance Monitoring Setup

##### APM Integration Migration

```php
<?php

namespace App\Services\Monitoring;

class PerformanceMonitoringService
{
    public function setupPerformanceMonitoring(): void
    {
        // Configure Laravel Telescope for development
        $this->configureTelescopeMonitoring();
        
        // Set up custom performance metrics
        $this->setupCustomMetrics();
        
        // Configure alerting thresholds
        $this->configureAlertingThresholds();
        
        // Set up automated reporting
        $this->setupAutomatedReporting();
    }
    
    private function setupCustomMetrics(): void
    {
        // Migration performance metrics
        Metrics::register('migration_duration_seconds', 'histogram', [
            'help' => 'Duration of migration operations in seconds',
            'labels' => ['migration_type', 'status']
        ]);
        
        Metrics::register('migration_records_processed_total', 'counter', [
            'help' => 'Total number of records processed during migrations',
            'labels' => ['migration_type', 'source']
        ]);
        
        // AI system metrics
        Metrics::register('ai_request_duration_seconds', 'histogram', [
            'help' => 'Duration of AI requests in seconds',
            'labels' => ['model', 'request_type']
        ]);
        
        Metrics::register('ai_cost_usd_total', 'counter', [
            'help' => 'Total cost of AI requests in USD',
            'labels' => ['model', 'provider']
        ]);
        
        // OCR processing metrics
        Metrics::register('ocr_processing_duration_seconds', 'histogram', [
            'help' => 'Duration of OCR processing in seconds',
            'labels' => ['screen_type', 'status']
        ]);
        
        Metrics::register('ocr_confidence_score', 'histogram', [
            'help' => 'OCR confidence scores',
            'labels' => ['screen_type']
        ]);
    }
    
    private function configureAlertingThresholds(): void
    {
        // Migration performance alerts
        Alert::create([
            'name' => 'Migration Duration Exceeded',
            'condition' => 'migration_duration_seconds > 1800', // 30 minutes
            'severity' => 'warning',
            'notification_channels' => ['email', 'slack']
        ]);
        
        Alert::create([
            'name' => 'Migration Failure Rate High',
            'condition' => 'rate(migration_records_processed_total{status="failed"}[5m]) > 0.1',
            'severity' => 'critical',
            'notification_channels' => ['email', 'slack', 'sms']
        ]);
        
        // AI cost alerts
        Alert::create([
            'name' => 'AI Cost Budget Exceeded',
            'condition' => 'ai_cost_usd_total > 100', // $100 monthly budget
            'severity' => 'warning',
            'notification_channels' => ['email']
        ]);
        
        // System resource alerts
        Alert::create([
            'name' => 'High Memory Usage',
            'condition' => 'memory_usage_percent > 90',
            'severity' => 'critical',
            'notification_channels' => ['email', 'slack']
        ]);
    }
}
```

#### 12.2.2 Migration Audit and Compliance

##### Audit Trail Migration

```php
<?php

namespace App\Services\Audit;

class MigrationAuditService
{
    public function setupAuditTrail(): void
    {
        // Create audit log table
        $this->createAuditLogTable();
        
        // Set up event listeners for audit logging
        $this->setupAuditEventListeners();
        
        // Configure audit data retention
        $this->configureAuditRetention();
        
        // Set up compliance reporting
        $this->setupComplianceReporting();
    }
    
    private function setupAuditEventListeners(): void
    {
        // Migration events
        Event::listen(MigrationStarted::class, function ($event) {
            AuditLog::create([
                'event_type' => 'migration_started',
                'user_id' => $event->userId,
                'resource_type' => $event->migrationType,
                'resource_id' => $event->migrationId,
                'event_data' => [
                    'source' => $event->source,
                    'options' => $event->options
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        });
        
        Event::listen(MigrationCompleted::class, function ($event) {
            AuditLog::create([
                'event_type' => 'migration_completed',
                'user_id' => $event->userId,
                'resource_type' => $event->migrationType,
                'resource_id' => $event->migrationId,
                'event_data' => [
                    'records_processed' => $event->recordsProcessed,
                    'duration_seconds' => $event->durationSeconds,
                    'errors' => $event->errors
                ],
                'created_at' => now()
            ]);
        });
        
        // Data access events
        Event::listen(DataAccessed::class, function ($event) {
            AuditLog::create([
                'event_type' => 'data_accessed',
                'user_id' => $event->userId,
                'resource_type' => $event->resourceType,
                'resource_id' => $event->resourceId,
                'event_data' => [
                    'access_type' => $event->accessType,
                    'fields_accessed' => $event->fieldsAccessed
                ],
                'ip_address' => request()->ip(),
                'created_at' => now()
            ]);
        });
    }
    
    private function configureAuditRetention(): void
    {
        // Set up automated cleanup of old audit logs
        Schedule::command('audit:cleanup')
            ->daily()
            ->at('01:00')
            ->description('Clean up audit logs older than retention period');
    }
    
    private function setupComplianceReporting(): void
    {
        // Generate monthly compliance reports
        Schedule::command('audit:generate-compliance-report')
            ->monthly()
            ->description('Generate monthly compliance report');
        
        // Generate data processing reports for GDPR compliance
        Schedule::command('audit:generate-gdpr-report')
            ->monthly()
            ->description('Generate GDPR compliance report');
    }
}

### 10.1 Validation and Monitoring

#### 10.1.1 Data Validation

**Comprehensive Data Audit**:

- Verify all migrated records for completeness
- Check data relationships and foreign keys
- Validate calculated fields and derived data
- Confirm user-specific data accuracy

**Performance Monitoring**:

- Monitor database query performance
- Track application response times
- Observe memory and CPU usage patterns
- Validate caching effectiveness

#### 10.1.2 User Acceptance

**User Feedback Collection**:

- Survey users about migration experience
- Collect feedback on data accuracy
- Identify any missing or incorrect information
- Gather suggestions for improvement

**Issue Resolution**:

- Address reported data discrepancies
- Fix any functional issues discovered
- Provide user support for migration questions
- Document common issues and solutions

### 10.2 System Optimization

#### 10.2.1 Performance Tuning

**Database Optimization**:

- Analyze query patterns and add indexes
- Optimize slow-performing queries
- Adjust database configuration parameters
- Implement additional caching strategies

**Application Optimization**:

- Profile application performance
- Optimize resource-intensive operations
- Implement lazy loading where appropriate
- Cache frequently accessed data

#### 10.2.2 Monitoring Setup

**Automated Monitoring**:

- Set up alerts for data inconsistencies
- Monitor external API health and performance
- Track user activity and system usage
- Implement error tracking and reporting

**Regular Maintenance**:

- Schedule periodic data validation checks
- Plan regular external data synchronization
- Maintain backup and recovery procedures
- Update migration documentation

---

## Conclusion

This updated Data Migration Plan provides a comprehensive framework for successfully migrating data into the modern UmamusumeCareerPlanner system built with Laravel 12, Tailwind CSS v4, and cutting-edge web technologies. The plan emphasizes data integrity, user privacy, accessibility compliance, and system reliability while leveraging modern architectural patterns and performance optimization techniques.

### Key Success Factors

#### Technical Excellence
- **Laravel 12 Features**: Strict mode compliance, asynchronous caching, advanced relationship management
- **Modern Frontend**: Tailwind CSS v4 with zero configuration, Progressive Web App capabilities, WCAG 2.2 AA compliance
- **Hybrid AI Integration**: Ollama local processing with AWS Bedrock fallback for optimal cost and privacy balance
- **Performance Optimization**: Redis caching strategies, database indexing, connection pooling, query optimization

#### Data Management Excellence
- **Comprehensive Validation**: Multi-layer validation with real-time feedback and error correction
- **Intelligent Caching**: Redis-based caching with TTL management, cache warming, and invalidation strategies
- **Event-Driven Architecture**: Laravel Events and Listeners for decoupled, scalable migration workflows
- **Audit and Compliance**: Complete audit trails, GDPR compliance, data retention policies

#### User Experience Excellence
- **Accessibility First**: WCAG 2.2 AA compliance throughout all migration interfaces
- **Real-time Feedback**: WebSocket-based progress updates with screen reader support
- **Intuitive Interfaces**: Drag-and-drop uploads, preview modes, error correction workflows
- **Privacy Protection**: Local-first architecture with explicit consent management

### Critical Implementation Considerations

#### External Dependencies
- **API Reliability**: umapyoi.net verified active, UmamusumeDB.com requires verification during Phase 1
- **Rate Limiting**: Respect external API limits with exponential backoff and intelligent caching
- **Data Quality**: Multi-source validation and community consensus for meta information
- **Fallback Strategies**: Graceful degradation when external services are unavailable

#### Performance and Scalability
- **Resource Management**: Memory usage under 2GB, processing rates above 500 records/minute
- **Concurrent Processing**: Support for up to 10 simultaneous user imports with queue management
- **Database Optimization**: Comprehensive indexing strategy and query optimization
- **Monitoring and Alerting**: Real-time performance monitoring with automated alerting

#### Security and Privacy
- **Data Encryption**: AES-256 encryption for sensitive data at rest and in transit
- **Access Control**: Role-based access control with comprehensive audit logging
- **File Security**: Malicious content scanning and secure temporary file handling
- **Privacy Compliance**: GDPR and CCPA compliance with user data portability

### Migration Success Metrics

#### Technical Metrics
- **Data Integrity**: 99.9% accuracy across all migration operations
- **Performance**: Page load times under 2 seconds, API responses under 500ms
- **Availability**: 99.5% uptime during migration periods
- **Error Rate**: Less than 0.1% migration failures with automatic recovery

#### User Experience Metrics
- **Accessibility**: 100% WCAG 2.2 AA compliance verification
- **User Satisfaction**: 90%+ satisfaction rating for migration experience
- **Completion Rate**: 95%+ successful completion rate for user data imports
- **Support Requests**: Less than 5% of users requiring migration assistance

#### Business Metrics
- **Migration Efficiency**: Complete game data migration within 30 minutes
- **User Adoption**: 80%+ of users successfully import their existing data
- **Cost Management**: AI processing costs under $50/month for typical usage
- **Community Integration**: 90%+ accuracy for community-sourced meta data

### Future Evolution and Maintenance

#### Continuous Improvement
- **Machine Learning**: AI model performance improvement based on user feedback
- **Community Integration**: Enhanced community data validation and contribution systems
- **Performance Optimization**: Ongoing database and caching optimization based on usage patterns
- **Feature Enhancement**: Regular updates to support new game features and mechanics

#### Scalability Preparation
- **Cloud Migration Ready**: Architecture supports future cloud deployment without major changes
- **Multi-tenant Support**: Foundation for supporting multiple users and organizations
- **API Expansion**: RESTful API design supports future mobile app and third-party integrations
- **Internationalization**: Unicode support and localization framework for global expansion

The phased approach ensures incremental progress with comprehensive validation at each step, reducing risk while maintaining system availability and user satisfaction. The modern technology stack provides a solid foundation for future growth and feature expansion while maintaining the local-first, privacy-focused approach that users expect.

---

### Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | January 10, 2026 | Development Team | Initial DMP document creation |
| 2.0 | January 10, 2026 | Development Team | Updated for Laravel 12, Tailwind CSS v4, modern architecture, AI integration, OCR processing, accessibility compliance, and comprehensive performance optimization |

### Technology Verification Status

| Technology | Version | Status | Verification Date |
|------------|---------|--------|-------------------|
| Laravel | 12.x | ✅ Verified | January 10, 2026 |
| Tailwind CSS | v4 | ✅ Verified | January 10, 2026 |
| umapyoi.net API | v1 | ✅ Active | January 10, 2026 |
| AWS Bedrock Claude | 4.5 | ✅ Available | January 10, 2026 |
| cloudstudio/ollama-laravel | 2.x | ✅ Compatible | January 10, 2026 |
| UmamusumeDB.com | Unknown | ⚠️ Requires Verification | Pending |

### Approval

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Data Architect | [Name] | [Signature] | [Date] |
| Database Administrator | [Name] | [Signature] | [Date] |
| Frontend Architect | [Name] | [Signature] | [Date] |
| AI Integration Lead | [Name] | [Signature] | [Date] |
| Accessibility Specialist | [Name] | [Signature] | [Date] |
| Project Manager | [Name] | [Signature] | [Date] |
