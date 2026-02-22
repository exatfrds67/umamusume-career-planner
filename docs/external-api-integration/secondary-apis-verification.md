# Secondary APIs Verification Report

**Verification Date**: January 2026  
**Task**: 4.2.1 - Verify Umalator.com and umamusumecalculator.com API availability  
**Status**: ❌ **No Public APIs Available for Either Site**

---

## Executive Summary

Both **Umalator.com** and **umamusumecalculator.com** are community-created web applications for Uma Musume Pretty
Derby. After comprehensive testing, **neither site provides a public API** for programmatic access. Both sites are
JavaScript-based web applications that serve data only through their interactive web interfaces.

### Key Findings

| Site | API Available | Web Scraping | Recommendation |
| --- | --- | --- | --- |
| Umalator.com | ❌ No | ⚠️ Allowed (robots.txt) | Not viable as API source |
| umamusumecalculator.com | ❌ No | ⚠️ Allowed (robots.txt) | Not viable as API source |

---

## Site 1: Umalator.com

### Overview

**URL**: <https://umalator.com>  
**Type**: Uma Musume Tools & Simulators  
**Framework**: JavaScript-based web application  
**Status**: ❌ **No Public API**

### robots.txt Analysis

```txt
User-agent: *
Allow: /
Allow: /tools/
Allow: /blog/

Sitemap: https://umalator.com/sitemap.xml

# Disallow crawling of admin or sensitive areas
Disallow: /admin/
Disallow: /private/

# Allow search engines to crawl all content
Crawl-delay: 1
```text

**Key Findings**:

- All public pages are allowed for crawling
- No `/api/` path mentioned (doesn't exist)
- 1-second crawl delay recommended
- Admin and private areas blocked

## API Endpoint Testing

| Endpoint Tested                         | HTTP Status | Result    |
| --------------------------------------- | ----------- | --------- |
| `https://umalator.com/api`              | 404         | Not Found |
| `https://umalator.com/api/v1`           | 404         | Not Found |
| `https://umalator.com/api/characters`   | 404         | Not Found |
| `https://umalator.com/data`             | 404         | Not Found |
| `https://umalator.com/data.json`        | 404         | Not Found |
| `https://umalator.com/static/data.json` | 404         | Not Found |
| `https://umalator.com/assets/data.json` | 404         | Not Found |
| `https://umalator.com/docs`             | 404         | Not Found |
| `https://umalator.com/api-docs`         | 404         | Not Found |

### Available Tools (Web-Only)

From sitemap.xml analysis:

| Tool                   | URL                              | Description                 |
| ---------------------- | -------------------------------- | --------------------------- |
| Race Simulator         | `/tools/race-simulator-jp`       | JP server race simulation   |
| Build Planner          | `/tools/build-planner`           | Training build optimization |
| Skill Visualizer       | `/tools/skill-visualizer-global` | Skill effect visualization  |
| Support Card Tier List | `/tools/support-card-tier-list`  | Card rankings and analysis  |
| Course Images          | `/tools/course-images`           | Race course layouts         |
| Roguelike Helper       | `/tools/roguelike-helper`        | Roguelike mode tools        |
| Umadle                 | `/tools/umadle`                  | Daily guessing game         |

### Site Characteristics

- **Open Source**: Mentioned as "community-driven open source project"
- **Data Source**: "Built on top of community-maintained datasets"
- **Contact**: [email protected]
- **Monetization**: Advertising for hosting costs only
- **Affiliation**: Not affiliated with Cygames

### Authentication Requirements

- No authentication required for web access
- No API keys or tokens available
- No documented API access

### Rate Limits

- **Web pages**: 1-second crawl delay recommended
- **API**: N/A (no API exists)

---

## Site 2: umamusumecalculator.com

### Overview

**URL**: <https://www.umamusumecalculator.com>  
**Type**: Training, Affinity, Legacy & Support Card Calculator  
**Framework**: JavaScript-based SPA (Single Page Application)  
**Status**: ❌ **No Public API**

### robots.txt Analysis

```txt
User-agent: *
Allow: /

Sitemap: https://www.umamusumecalculator.com/sitemap.xml

# Uma Musume Calculator - Training, Affinity, Legacy & Support Card Calculator
# Professional Uma Musume Pretty Derby calculator suite
# https://www.umamusumecalculator.com
```

**Key Findings**:

- All pages allowed for crawling
- No API restrictions mentioned (no API exists)
- Multi-language support (EN, JA, KO, ZH-CN, ZH-TW)

## API Endpoint Testing

| Endpoint Tested                                 | HTTP Status | Result       |
| ----------------------------------------------- | ----------- | ------------ |
| `https://umamusumecalculator.com/api`           | 500         | Server Error |
| `https://www.umamusumecalculator.com/api`       | 500         | Server Error |
| `https://umamusumecalculator.com/data`          | 500         | Server Error |
| `https://www.umamusumecalculator.com/data.json` | 500         | Server Error |

**Note**: The 500 errors suggest the site is a JavaScript SPA that doesn't handle non-existent routes gracefully, rather
than having a disabled API.

### Available Calculators (Web-Only)

From sitemap.xml analysis:

| Calculator                  | URL Pattern                   | Languages                |
| --------------------------- | ----------------------------- | ------------------------ |
| Affinity Calculator         | `/{lang}/affinity-calculator` | EN, JA, KO, ZH-CN, ZH-TW |
| Training Calculator         | (mentioned in about page)     | Multi-language           |
| Support Card Analysis       | (mentioned in about page)     | Multi-language           |
| Race Performance Prediction | (mentioned in about page)     | Multi-language           |
| Legacy Calculator           | (mentioned in about page)     | Multi-language           |
| Gacha Calculator            | (mentioned in about page)     | Multi-language           |
| Rank Calculator             | (mentioned in about page)     | Multi-language           |
| Deck Calculator             | (mentioned in about page)     | Multi-language           |

### Site Characteristics

- **Privacy**: "We don't store personal data"
- **Free Access**: "All calculators are completely free"
- **Community Input**: "Built with input from experienced players, data miners"
- **Updates**: "Continuously update algorithms and formulas"
- **Contact**: No direct contact method (community forums mentioned)

### Authentication Requirements

- No authentication required
- No API keys available
- No documented API access

### Rate Limits

- No documented rate limits
- No API to rate limit

---

## Impact on System Design

### Original Design Assumptions

The requirements document assumed:

- Umalator.com would provide race simulation data
- umamusumecalculator.com would provide calculation tools
- Both would serve as secondary API sources with automatic failover

### Revised Reality

| Assumption | Reality |
| --- | --- |
| Umalator.com has API | ❌ No API - web tools only |
| umamusumecalculator.com has API | ❌ No API - web calculators only |
| Multiple fallback sources available | ❌ Only umapyoi.net has public API |

### Updated API Source Priority

```php
private const API_SOURCES = [
    'umapyoi' => [
        'priority' => 1,
        'base_url' => 'https://api.umapyoi.net/api/v1',
        'timeout' => 5,
        'rate_limit' => 100,
        'status' => 'active',
    ],
    // UmamusumeDB.com - No API (robots.txt blocks /api/)
    // Umalator.com - No API (404 on all API endpoints)
    // umamusumecalculator.com - No API (500 errors, SPA only)
];
```text

---

## Recommendations

### 1. Single Source Strategy (Recommended)

Since **umapyoi.net is the only viable API source**, implement a robust single-source strategy:

**Implementation**:

- Strengthen umapyoi.net integration with comprehensive error handling
- Implement aggressive caching with extended TTLs
- Add circuit breaker pattern for API failures
- Provide graceful degradation to cached data

**Benefits**:

- Simpler architecture
- No data reconciliation needed
- Reduced complexity

**Risks**:

- Single point of failure
- Dependent on umapyoi.net availability

### 2. Enhanced Caching Strategy

Compensate for lack of fallback APIs with robust caching:

```php
private const CACHE_TTL = [
    'character_data' => 172800,    // 48 hours (extended from 24)
    'support_cards' => 86400,      // 24 hours (extended from 12)
    'meta_rankings' => 43200,      // 12 hours (extended from 6)
    'race_data' => 259200,         // 72 hours (extended from 48)
    'skills' => 172800,            // 48 hours (extended from 24)
];
```

### 3. Local Data Seeding

Seed the database with essential static data:

- All character base stats and aptitudes
- All support card definitions
- All skill definitions
- Race course data

This ensures the application functions even when umapyoi.net is unavailable.

### 4. Community Data Integration (Future)

Consider alternative approaches for future enhancement:

| Approach                | Feasibility | Notes                        |
| ----------------------- | ----------- | ---------------------------- |
| GameWith API            | Unknown     | Japanese gaming database     |
| Gamerch API             | Unknown     | Japanese wiki platform       |
| Community Discord bots  | Low         | Informal, unreliable         |
| Direct game data mining | Complex     | Requires reverse engineering |

### 5. Web Scraping (Not Recommended)

While both sites allow web crawling, scraping is **not recommended** because:

- Fragile HTML parsing
- Ethical concerns
- Maintenance burden
- Potential blocking
- No structured data format

---

## Configuration Updates Required

### Update `config/external-apis.php`

```php
<?php

return [
    'sources' => [
        'umapyoi' => [
            'enabled' => true,
            'status' => 'active',
            'base_url' => 'https://api.umapyoi.net/api/v1',
            'timeout' => 5,
            'rate_limit' => 100,
            'api_available' => true,
            'last_verified' => '2026-01-XX',
        ],
        
        'umamusumedb' => [
            'enabled' => false,
            'status' => 'unavailable',
            'reason' => 'No public API - robots.txt blocks /api/',
            'base_url' => 'https://umamusumedb.com',
            'api_available' => false,
            'last_verified' => '2026-01-XX',
        ],
        
        'umalator' => [
            'enabled' => false,
            'status' => 'unavailable',
            'reason' => 'No public API - web tools only',
            'base_url' => 'https://umalator.com',
            'api_available' => false,
            'web_tools_available' => true,
            'tools' => [
                'race-simulator-jp',
                'build-planner',
                'skill-visualizer-global',
                'support-card-tier-list',
            ],
            'last_verified' => '2026-01-XX',
            'notes' => 'Open source community project with web-based tools',
        ],
        
        'umamusumecalculator' => [
            'enabled' => false,
            'status' => 'unavailable',
            'reason' => 'No public API - JavaScript SPA only',
            'base_url' => 'https://www.umamusumecalculator.com',
            'api_available' => false,
            'web_calculators_available' => true,
            'calculators' => [
                'affinity-calculator',
                'training-calculator',
                'legacy-calculator',
                'support-card-analysis',
            ],
            'languages' => ['en', 'ja', 'ko', 'zh-CN', 'zh-TW'],
            'last_verified' => '2026-01-XX',
            'notes' => 'Multi-language calculator suite, no API access',
        ],
    ],
    
    'fallback_strategy' => 'cache_only',
    'cache_extension_on_failure' => true,
    'staleness_warning_threshold' => 86400, // 24 hours
];
```text

---

## Task Status Updates

### Completed Tasks

- ✅ **4.1.1**: Verify UmamusumeDB.com API availability → No API
- ✅ **4.2.1**: Verify Umalator.com and umamusumecalculator.com → No APIs

### Skipped Tasks

- ⏭️ **4.1.2**: Implement UmamusumeDB.com client → Skipped (no API)
- ⏭️ **4.2.2**: Implement additional source clients → Skipped (no APIs)

### Impact on Phase 4

**Phase 4: Secondary API Source Integration** is effectively complete with negative findings:

- No secondary API sources are available
- System design should be updated to reflect single-source architecture
- Enhanced caching and local data seeding become critical

---

## Conclusion

**Neither Umalator.com nor umamusumecalculator.com can be used as secondary API sources** for the External API
Integration feature. Both sites are web-only applications without public APIs.

### Final Recommendations

1. ✅ **Accept umapyoi.net as sole API source** - It's the only verified public API
2. ✅ **Implement robust caching** - Extended TTLs and aggressive cache warming
3. ✅ **Add local data seeding** - Essential data for offline functionality
4. ✅ **Update system design** - Remove multi-source fallback assumptions
5. ⚠️ **Monitor for new APIs** - Community may develop new sources in future
6. ⚠️ **Consider contacting site owners** - May obtain data exports or API access

### Architecture Simplification

The lack of secondary APIs actually simplifies the architecture:

| Original Design                 | Simplified Design     |
| ------------------------------- | --------------------- |
| Multi-source fallback           | Single source + cache |
| Data reconciliation             | Not needed            |
| Conflict resolution             | Not needed            |
| Source priority management      | Not needed            |
| Multiple client implementations | Single client         |

This reduces complexity while maintaining reliability through robust caching.

---

## References

### Umalator.com

- **Website**: <https://umalator.com>
- **robots.txt**: <https://umalator.com/robots.txt>
- **Sitemap**: <https://umalator.com/sitemap.xml>
- **Contact**: [email protected]
- **Type**: Open source community project

### umamusumecalculator.com

- **Website**: <https://www.umamusumecalculator.com>
- **robots.txt**: <https://umamusumecalculator.com/robots.txt>
- **Sitemap**: <https://www.umamusumecalculator.com/sitemap.xml>
- **About**: <https://www.umamusumecalculator.com/about>
- **Type**: Multi-language calculator suite

### Related Documents

- UmamusumeDB API Verification: `.kiro/specs/external-api-integration/umamusumedb-api-verification.md`
- Requirements: `.kiro/specs/external-api-integration/requirements.md`
- Design: `.kiro/specs/external-api-integration/design.md`

