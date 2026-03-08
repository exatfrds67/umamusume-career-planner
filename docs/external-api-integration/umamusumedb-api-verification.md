# UmamusumeDB.com API Verification Report

**Verification Date**: January 2026  
**Task**: 4.1.1 - Verify UmamusumeDB.com API availability  
**Status**: ❌ **No Public API Available**

---

## Executive Summary

UmamusumeDB.com is a fan-created database website for Uma Musume Pretty Derby game data. After comprehensive testing,
**no public API is available** for programmatic access. The site explicitly blocks API access via robots.txt and serves
data only through static HTML pages built with Astro framework.

---

## Verification Results

### 1. API Endpoint Testing

| Endpoint Tested | Result | Notes |
| --- | --- | --- |
| `https://umamusumedb.com/api` | ❌ Blocked | Redirects to homepage |
| `https://umamusumedb.com/api/v1` | ❌ Blocked | robots.txt disallows |
| `https://umamusumedb.com/api/characters` | ❌ Blocked | robots.txt disallows |
| `https://api.umamusumedb.com` | ❌ N/A | Subdomain does not exist |

### 2. robots.txt Analysis

The site's robots.txt explicitly disallows API access:

```txt
User-agent: *
Allow: /
Disallow: /api/
Disallow: /admin/

Crawl-delay: 1
```text

**Key Findings**:

- `/api/` path is explicitly disallowed for all user agents
- `/admin/` path is also blocked
- 1-second crawl delay is specified for respectful crawling
- Search engine bots (Googlebot, Bingbot, etc.) are allowed full access to public pages
- Several SEO bots (SemrushBot, DotBot, AhrefsBot) are completely blocked

### 3. Authentication Requirements

- **No authentication required** for public web pages
- **API access is blocked** regardless of authentication
- No API keys or tokens are documented or available

### 4. Rate Limits

- **Web pages**: 1-second crawl delay recommended
- **API**: N/A (blocked)
- No documented rate limits for web scraping

---

## Available Data (Web Pages Only)

### Website Structure

The site is built with **Astro** (static site generator) and provides:

| Content Type  | URL Pattern           | Data Available                                       |
| ------------- | --------------------- | ---------------------------------------------------- |
| Characters    | `/characters/{slug}/` | Stats, aptitudes, skills, training tips              |
| Support Cards | `/cards/{slug}/`      | Effects, skills, usage tips                          |
| Tools         | `/tools/`             | Factor Calculator, Training Calculator, Deck Builder |
| Skills        | `/skills/`            | Skill database                                       |

### Character Data Structure

From `/characters/special_week_2025/`:

```json
{
  "name": "Special Week",
  "japanese_name": "スペシャルウィーク",
  "stats": {
    "speed": 77,
    "stamina": 76,
    "power": 77,
    "guts": 102,
    "wisdom": 97
  },
  "aptitudes": {
    "distance": {
      "sprint": "F",
      "mile": "C",
      "medium": "A",
      "long": "A"
    },
    "surface": {
      "turf": "A",
      "dirt": "G"
    },
    "strategy": {
      "front_runner": "C",
      "pace_chaser": "A",
      "late_surger": "B",
      "end_closer": "C"
    }
  },
  "unique_skill": {
    "name": "Eat Up and Work Hard♪",
    "japanese": "食い下がり",
    "effect": "Stamina recovery when surrounded"
  }
}
```text

### Support Card Data Structure

From `/cards/kitasan_black_ssr/`:

```json
{
  "name": "Kitasan Black [Road to the Top]",
  "japanese_name": "キタサンブラック【迫る熱に押されて】",
  "rarity": "SSR",
  "type": "Speed",
  "effects": {
    "friendship_bonus": "+35",
    "training_bonus": "+25",
    "initial_bond": "+35",
    "motivation_bonus": "+15",
    "speed_bonus": "+3"
  },
  "skills": ["Arc Maestro", "Curve Specialist", "Speed Star"]
}
```text

### Content Statistics (from homepage)

- **60** Characters
- **25** Support Cards
- **21** SSR Cards
- **47** Skills
- **7** Categories

---

## Alternative Approaches

### Option 1: Web Scraping (Not Recommended)

**Pros**:

- Data is available in structured HTML
- Sitemap provides all URLs

**Cons**:

- Violates robots.txt intent (blocks `/api/`)
- Requires HTML parsing (fragile)
- 1-second crawl delay limits throughput
- May be blocked if detected
- Ethical concerns

**Recommendation**: ❌ Not recommended due to ethical and reliability concerns

### Option 2: Use umapyoi.net as Primary Source (Recommended)

**Pros**:

- Verified public API available
- 100 requests/minute rate limit
- No authentication required
- Already integrated as primary source

**Cons**:

- Single point of failure

**Recommendation**: ✅ **Recommended** - Continue using umapyoi.net as primary source

### Option 3: Manual Data Import

**Pros**:

- One-time data extraction
- No ongoing API dependency
- Can be cached indefinitely

**Cons**:

- Requires manual updates
- Data may become stale
- Labor-intensive

**Recommendation**: ⚠️ Consider for static reference data only

### Option 4: Contact Site Owner

**Pros**:

- May obtain API access or data export
- Establishes legitimate partnership

**Cons**:

- No guarantee of response
- May take time

**Recommendation**: ⚠️ Worth attempting via GitHub Issues (mentioned in footer)

---

## Impact on System Design

### Original Design Assumptions

The requirements document assumed:

- UmamusumeDB.com would be a secondary API source
- Automatic failover from umapyoi.net to UmamusumeDB.com
- Data reconciliation between sources

### Revised Recommendations

1. **Remove UmamusumeDB.com from API fallback chain**
   - No programmatic API access available
   - Cannot be used for automatic failover

2. **Strengthen umapyoi.net integration**
   - Implement robust caching (already planned)
   - Extend cache TTLs for offline resilience
   - Add comprehensive error handling

3. **Consider alternative secondary sources**
   - Verify Umalator.com API availability (Task 4.2.1)
   - Verify umamusumecalculator.com API availability (Task 4.2.1)
   - Research other community APIs

4. **Implement local data fallback**
   - Seed database with essential character/card data
   - Use cached data when all APIs unavailable
   - Show staleness indicators to users

---

## Configuration Updates Required

### Update `config/external-apis.php`

```php
'umamusumedb' => [
    'enabled' => false,
    'status' => 'unavailable',
    'reason' => 'No public API - robots.txt blocks /api/',
    'base_url' => 'https://umamusumedb.com',
    'api_available' => false,
    'web_scraping_allowed' => false,
    'last_verified' => '2026-01-XX',
    'notes' => 'Static Astro site with rich data but no API access',
],
```

### Update API Source Priority

```php
private const API_SOURCES = [
    'umapyoi' => [
        'priority' => 1,
        'base_url' => 'https://api.umapyoi.net/api/v1',
        'timeout' => 5,
        'rate_limit' => 100,
        'status' => 'active',
    ],
    // UmamusumeDB removed from fallback chain
    'umalator' => [
        'priority' => 2, // Promoted from 3
        'base_url' => 'https://umalator.com/api',
        'timeout' => 5,
        'rate_limit' => 30,
        'status' => 'requires_verification',
    ],
];
```text

---

## Sitemap Reference

Available pages from `sitemap-0.xml`:

### Characters (60+)

- `/characters/special_week_2025/`
- `/characters/silence_suzuka_2025/`
- `/characters/gold_ship_2025/`
- `/characters/tokai_teio_2025/`
- `/characters/mejiro_mcqueen_2025/`
- ... and more

### Support Cards (25+)

- `/cards/kitasan_black_ssr/`
- `/cards/super_creek_ssr/`
- `/cards/fine_motion_ssr/`
- `/cards/symboli_rudolf_ssr/`
- ... and more

---

## Conclusion

**UmamusumeDB.com cannot be used as a secondary API source** for the External API Integration feature. The site
explicitly blocks API access and provides data only through static web pages.

### Recommended Actions

1. ✅ Mark Task 4.1.1 as complete with "API unavailable" finding
2. ⚠️ Skip Task 4.1.2 (Implement UmamusumeDB.com client) - not feasible
3. ✅ Proceed with Task 4.2.1 to verify alternative sources
4. ✅ Update system design to remove UmamusumeDB.com from fallback chain
5. ✅ Strengthen umapyoi.net as sole primary source with robust caching

---

## References

- **Website**: <https://umamusumedb.com>
- **robots.txt**: <https://umamusumedb.com/robots.txt>
- **Sitemap**: <https://umamusumedb.com/sitemap-index.xml>
- **GitHub Issues**: Available for feedback (mentioned in site footer)
- **Framework**: Astro (static site generator)
- **Last Updated**: December 31, 2025 (per sitemap)

