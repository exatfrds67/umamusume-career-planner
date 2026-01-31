# Deprecated Seeders - REMOVED

**Date**: January 30, 2026  
**Status**: All deprecated seeders have been consolidated and removed

## Summary

All legacy seeders have been **consolidated into active seeders** and **deleted**. The functionality was merged into better, API-driven versions.

## Consolidation Complete

### Skills → `UcpSkillsSeeder`

- Removed: `ComprehensiveSkillSeeder.php`, `RealUmaMusumeSkillsSeeder.php`
- Result: 1730 skills (61 curated + 1700 from gametora API)
- Features: Evolution relationships, metadata preservation, smart deduplication

### Support Cards → `UcpSupportCardsSeeder`

- Removed: `SupportCardSeeder.php`, `EnrichSupportCardsSeeder.php`
- Result: 200+ cards from umapyoi.net API with Game8.co enrichment
- Features: Meta tiers, training bonuses, skill hints

### Characters → `EnhancedRealUmaMusumeCharactersSeeder`

- Removed: `RealUmaMusumeCharactersSeeder.php`, `UcpAptitudesSeeder.php`
- Result: 161 characters with 1008 aptitudes
- Features: Baseline stats, growth rates, local images

## Active Seeders

Run via `php artisan db:seed`:

1. AdminUserSeeder
2. UcpSkillsSeeder
3. UcpSupportCardsSeeder
4. EnhancedRealUmaMusumeCharactersSeeder

---

**Note**: This directory is kept for documentation only. All deprecated files have been removed.
