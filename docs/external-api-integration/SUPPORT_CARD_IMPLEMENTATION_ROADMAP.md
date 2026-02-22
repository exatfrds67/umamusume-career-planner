# Support Card Detailed Information - Implementation Roadmap

**Document Version**: 1.0.0  
**Date**: 2026-01-25  
**Status**: Planning  

## Executive Summary

This document outlines the roadmap for implementing comprehensive support card information in the Uma Musume Career
Planner, addressing the current data limitations from the umapyoi.net API.

## Current Status

### ✅ Completed (Phase 0)

- [x] Rarity inference from card ID ranges (R/SR/SSR)
- [x] Basic card display (name, image, character, rarity)
- [x] GameTora external linking
- [x] Filtering by rarity
- [x] Sorting by ID, name, and rarity
- [x] Import workflow to user collection

### ❌ Missing Data

- Card type (Speed/Stamina/Power/Guts/Wisdom/Friend)
- Stat bonuses (speed, stamina, power, guts, wisdom)
- Friendship and event bonuses
- Unique effects and abilities
- Skill hints provided
- Meta tier rankings
- Deck synergies
- Recommended scenarios

## Infrastructure Already in Place

### Database Schema

- ✅ `ucp_support_cards` table with 40+ fields
- ✅ Supports all required data types (stats, effects, skills, meta)
- ✅ JSON fields for complex data (unique_effects, skill_hints, synergies)
- ✅ Performance indexes on key fields

### Models & Services

- ✅ `SupportCardDefinition` model with full property definitions
- ✅ `SupportCardMetaService` for tier rankings and updates
- ✅ `SupportCardDeckService` for deck building
- ✅ `ExternalDataService` for API integration
- ✅ Seeder examples with proper data structure

## Implementation Phases

### Phase 1: Manual Database Seeding (IMMEDIATE - 2-3 days)

**Goal**: Populate database with detailed information for all 487 support cards

**Approach**: Manual data entry using community sources

**Data Sources**:

1. Game8.co - <https://game8.co/games/Umamusume-Pretty-Derby/archives/536715>
2. AppMedia - <https://appmedia.jp/umamusume>
3. GameWith - <https://gamewith.jp/uma-musume/>
4. Community wikis

**Tasks**:

1. Create `resources/data/support-cards.json` with structured card data
2. Expand `UcpSupportCardsSeeder.php` to load from JSON
3. Populate for all 487 cards:
   - Card type (Speed/Stamina/Power/Guts/Wisdom/Friend)
   - Rarity (already inferred, but validate)
   - Stat bonuses (at max level/limit break)
   - Friendship bonus
   - Event bonuses
   - Unique effects (text description)
   - Skill hints provided (array of skill names)
   - Meta tier (S+, S, A, B, C)
4. Run seeder to populate database
5. Update External Data Browser to show detailed info

**Deliverables**:

- JSON data file with all 487 cards
- Enhanced seeder
- Updated UI to display detailed information
- Documentation of data sources

**Estimated Effort**: 16-24 hours

- Data collection: 8-12 hours
- JSON structuring: 4-6 hours
- Seeder implementation: 2-3 hours
- UI updates: 2-3 hours

### Phase 2: Enhanced Display & Filtering (SHORT-TERM - 1-2 days)

**Goal**: Leverage detailed data for better user experience

**Tasks**:

1. Add card type filter (Speed/Stamina/Power/Guts/Wisdom/Friend)
2. Add meta tier filter (S+, S, A, B, C)
3. Show stat bonuses in card modal
4. Display unique effects and skill hints
5. Add "Compare Cards" feature
6. Show deck synergy suggestions

**Deliverables**:

- Enhanced filtering options
- Detailed card modal with all information
- Card comparison tool
- Synergy recommendations

**Estimated Effort**: 8-12 hours

### Phase 3: GameTora Integration (MEDIUM-TERM - 3-5 days)

**Goal**: Automate data updates and validation

**Approach**: Web scraping with proper rate limiting and caching

**Tasks**:

1. Create `GameToraScraperService` using Symfony DomCrawler
2. Implement card page parsing for:
   - Stat bonuses
   - Effects and abilities
   - Skill hints
   - Images
3. Store raw data in `ExternalData` table
4. Create `SupportCardDataIntegrationService` to map external → internal
5. Implement conflict resolution (manual data vs. scraped data)
6. Add admin panel for data review and approval
7. Schedule daily sync job

**Deliverables**:

- GameTora scraper service
- Data integration pipeline
- Admin review interface
- Automated sync job

**Estimated Effort**: 24-32 hours

**Legal Considerations**:

- Review GameTora ToS
- Implement respectful rate limiting (1 request per 2 seconds)
- Cache aggressively (7-day TTL)
- Provide attribution
- Consider reaching out for partnership

### Phase 4: Community Contributions (LONG-TERM - 1-2 weeks)

**Goal**: Enable community-driven data accuracy

**Tasks**:

1. Create admin panel for card data management
2. Implement user contribution workflow:
   - Submit corrections
   - Provide evidence (screenshots, links)
   - Moderation queue
3. Add confidence scoring system
4. Implement voting/verification mechanism
5. Create public API for data access

**Deliverables**:

- Admin panel
- Contribution workflow
- Moderation tools
- Public API

**Estimated Effort**: 40-60 hours

## Data Structure

### JSON Format for Manual Seeding

```json
{
  "cards": [
    {
      "id": 30001,
      "name": "[Japan's Number 1 Stage]",
      "internal_id": "support_30001",
      "card_type": "speed",
      "rarity": "SSR",
      "character_name": "Special Week",
      "character_id": 1001,
      "gametora": "30001-special-week",
      "max_level": 50,
      "max_limit_break": 4,
      "stats": {
        "speed_bonus": 35,
        "stamina_bonus": 0,
        "power_bonus": 10,
        "guts_bonus": 0,
        "wit_bonus": 0
      },
      "bonuses": {
        "friendship_bonus": 20,
        "event_recovery_bonus": 15,
        "event_effect_bonus": 20,
        "training_effect_bonus": 15
      },
      "unique_effects": [
        "Speed training boost (high)",
        "Race bonus for speed races",
        "Special events with Special Week"
      ],
      "skill_hints": [
        "Go with the Flow",
        "Speed Star",
        "Acceleration"
      ],
      "meta_tier": "S+",
      "deck_synergies": ["30002", "30005", "20015"],
      "recommended_scenarios": ["URA Finals", "Aoharu Cup"],
      "is_limited": false,
      "release_date": "2021-02-24",
      "artwork_url": "https://gametora.com/images/umamusume/supports/tex_support_card_30001.png"
    }
  ]
}
```text

## Priority Cards for Phase 1

Focus on most-used cards first:

### Tier S+ (Must have immediately)

- Top 20 most-used SSR cards
- Essential for competitive play

### Tier S (High priority)

- Next 30 popular SSR cards
- Common SR cards

### Tier A-C (Medium priority)

- Remaining SSR cards
- All SR cards
- Popular R cards

### Tier D (Low priority)

- Rarely used R cards
- Event-specific cards

## Success Metrics

### Phase 1 Success Criteria

- [ ] All 487 cards have rarity data
- [ ] Top 100 cards have complete stat information
- [ ] All SSR cards have unique effects documented
- [ ] Meta tier assigned to top 150 cards
- [ ] Skill hints documented for top 100 cards

### Phase 2 Success Criteria

- [ ] Card type filter functional
- [ ] Meta tier filter functional
- [ ] Detailed modal shows all available data
- [ ] User satisfaction with data completeness >80%

### Phase 3 Success Criteria

- [ ] GameTora scraper successfully parses 95%+ of cards
- [ ] Daily sync runs without errors
- [ ] Data conflicts resolved automatically 90%+ of time
- [ ] Admin review queue manageable (<10 items/day)

### Phase 4 Success Criteria

- [ ] Community contributions >10/week
- [ ] Data accuracy >95% (verified)
- [ ] Moderation queue processed within 24 hours
- [ ] Public API usage >100 requests/day

## Risk Mitigation

### Risk: Manual data entry errors

**Mitigation**:

- Cross-reference multiple sources
- Implement validation rules
- Community review process

### Risk: GameTora ToS violation

**Mitigation**:

- Review ToS carefully
- Implement respectful scraping
- Seek partnership
- Have fallback plan (manual updates)

### Risk: Data becomes outdated

**Mitigation**:

- Automated sync jobs
- Community contributions
- Version tracking
- Change notifications

### Risk: Performance impact

**Mitigation**:

- Aggressive caching
- Database indexing
- Lazy loading
- CDN for images

## Resource Requirements

### Development Time

- Phase 1: 16-24 hours
- Phase 2: 8-12 hours
- Phase 3: 24-32 hours
- Phase 4: 40-60 hours
- **Total**: 88-128 hours (11-16 days)

### Infrastructure

- Storage: +50MB for card images
- Database: +5MB for card data
- Cache: +10MB for scraped data
- Bandwidth: +100MB/month for scraping

### Team

- 1 Backend Developer (data pipeline)
- 1 Frontend Developer (UI enhancements)
- 1 Data Curator (manual entry, validation)
- 1 Community Manager (contributions, moderation)

## Next Steps

1. **Immediate**: Start Phase 1 manual seeding
   - Create JSON structure
   - Begin data collection from Game8.co
   - Populate top 50 cards

2. **This Week**: Complete Phase 1
   - Finish all 487 cards
   - Run seeder
   - Update UI

3. **Next Week**: Begin Phase 2
   - Enhanced filtering
   - Detailed display
   - Card comparison

4. **Month 2**: Phase 3 planning
   - GameTora ToS review
   - Scraper architecture
   - Integration design

## Conclusion

The infrastructure for comprehensive support card data is **already in place**. The primary task is **data population**,
which can be accomplished through manual seeding in the short term, with automation and community contributions in the
long term.

**Recommended Action**: Start Phase 1 immediately with focus on top 100 most-used cards.

## Related Documentation

- [Support Card Data Limitation](./SUPPORT_CARD_DATA_LIMITATION.md)
- [Support Card Rarity Solution](./SUPPORT_CARD_RARITY_SOLUTION.md)
- [External Data Browser Filters](./EXTERNAL_DATA_BROWSER_FILTERS.md)

## Change Log

### Version 1.0.0 (2026-01-25)

- Initial roadmap created
- Defined 4 implementation phases
- Estimated effort and resources
- Identified data sources and structure
- Prioritized cards for initial seeding

