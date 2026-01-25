# Implementation Plan: Umamusume Career Planner v2.1.0

## Overview

This implementation plan covers the v2.1.0 enhancements for the Umamusume Career Planner, focusing on performance optimization, accessibility compliance, PWA enhancements, advanced analytics, and comprehensive testing. The plan is organized into phases with incremental progress and checkpoints.

**Technology Stack:**

- Laravel 12, PHP 8.4
- Livewire 3, Alpine.js 3, Tailwind CSS v4
- MySQL 8.0+, Redis 6.0+
- Pest v4 for testing, Playwright for E2E
- Vite 7+ for build

---

## Tasks

- [ ] 1. Performance Optimization Foundation
  - [ ] 1.1 Implement tiered cache strategy with Redis L2 and memory L1
    - Create `app/Services/Cache/TieredCacheStrategy.php`
    - Configure Redis databases (cache DB 1, sessions DB 2, queues DB 3)
    - Implement cache key naming convention with prefixes and versions
    - _Requirements: REQ-1.1, REQ-1.2, REQ-1.3_
  
  - [ ] 1.2 Implement cache invalidation with tags
    - Create cache tag system for character, training, skill entities
    - Implement event-driven cache invalidation listeners
    - Add cache monitoring middleware
    - _Requirements: REQ-1.4, REQ-1.5_
  
  - [ ]* 1.3 Write property tests for cache consistency
    - **Property 1: Cache Tiering Consistency**
    - **Property 2: Cache Invalidation Completeness**
    - **Validates: Requirements 1.2, 1.4**

- [ ] 2. Database Query Optimization
  - [ ] 2.1 Create performance indexes migration
    - Add indexes on foreign keys (character_id, user_id)
    - Add indexes on status fields (run_status, skill_status)
    - Add composite indexes for common query patterns
    - _Requirements: REQ-2.2_
  
  - [ ] 2.2 Implement eager loading in repositories
    - Update `EloquentCharacterRepository` with eager loading
    - Implement cursor-based pagination for large datasets
    - Add database-level aggregation for calculations
    - _Requirements: REQ-2.1, REQ-2.3, REQ-2.4_
  
  - [ ] 2.3 Add query monitoring with Telescope
    - Create `SlowQueryWatcher` for queries >100ms
    - Configure EXPLAIN plan logging
    - Add N+1 query detection
    - _Requirements: REQ-2.5_
  
  - [ ]* 2.4 Write property tests for query optimization
    - **Property 3: Query Count Reduction**
    - **Validates: Requirements 2.1**

- [ ] 3. Checkpoint - Performance Foundation
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 4. API Response Caching and Circuit Breaker
  - [ ] 4.1 Implement circuit breaker pattern
    - Create `app/Services/ExternalAPI/CircuitBreaker.php`
    - Implement CLOSED, OPEN, HALF_OPEN states
    - Configure failure threshold (5 failures) and recovery timeout (60s)
    - _Requirements: REQ-3.2_
  
  - [ ] 4.2 Implement request coalescing
    - Add cache locks for concurrent API requests
    - Implement stale-while-revalidate pattern
    - Create background refresh job
    - _Requirements: REQ-3.3, REQ-3.4_
  
  - [ ] 4.3 Create external API caching service
    - Implement TTL-based caching (skills 24h, characters 12h, meta 6h)
    - Add freshness indicators for stale cache
    - Create `RefreshExternalDataJob`
    - _Requirements: REQ-3.1_
  
  - [ ]* 4.4 Write property tests for API caching
    - **Property 5: API Response Caching Round-Trip**
    - **Property 6: Request Coalescing Deduplication**
    - **Validates: Requirements 3.1, 3.4**

- [ ] 5. Frontend Performance Optimization
  - [ ] 5.1 Configure Vite code splitting
    - Set up route-based chunks (dashboard, character, training)
    - Configure vendor chunk extraction
    - Set bundle size budget (<200KB gzipped)
    - _Requirements: REQ-4.2_
  
  - [ ] 5.2 Implement lazy loading for images
    - Add responsive images with srcset
    - Implement lazy loading for below-fold images
    - Configure WebP format with JPEG fallback
    - _Requirements: REQ-4.3_
  
  - [ ] 5.3 Optimize Livewire interactions
    - Implement optimistic UI updates
    - Batch Livewire updates where possible
    - Add wire:loading states
    - _Requirements: REQ-4.4_
  
  - [ ]* 5.4 Write performance benchmark tests
    - **Property 29: Page Load Time Bounds**
    - **Validates: Requirements 4.1**

- [ ] 6. APM Integration
  - [ ] 6.1 Create APM service and custom watchers
    - Create `app/Services/Monitoring/APMService.php`
    - Implement `CachePerformanceWatcher`
    - Implement `ExternalAPIWatcher`
    - _Requirements: REQ-5.1, REQ-5.2_
  
  - [ ] 6.2 Implement metrics collection
    - Track request throughput, error rates, response times
    - Store metrics with 1-minute granularity
    - Create performance trends dashboard
    - _Requirements: REQ-5.3, REQ-5.5_
  
  - [ ] 6.3 Implement alerting system
    - Configure thresholds (slow requests >2s, error rate >1%)
    - Integrate with Laravel Notifications (email, Slack)
    - Create alert dashboard
    - _Requirements: REQ-5.4_

- [ ] 7. Checkpoint - Performance Complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 8. Accessibility - WCAG 2.2 AA Compliance
  - [ ] 8.1 Create accessible component library
    - Create `resources/views/components/accessible/button.blade.php`
    - Create `resources/views/components/accessible/form-field.blade.php`
    - Create `resources/views/components/accessible/modal.blade.php`
    - Add focus styles with 3:1 contrast ratio
    - _Requirements: REQ-6.1, REQ-6.4, REQ-6.5_
  
  - [ ] 8.2 Implement semantic HTML structure
    - Add skip-to-main link
    - Implement proper landmark regions (header, nav, main, footer)
    - Add ARIA labels for multiple navigation regions
    - _Requirements: REQ-6.2_
  
  - [ ] 8.3 Verify color contrast compliance
    - Audit all text for 4.5:1 contrast ratio
    - Audit large text for 3:1 contrast ratio
    - Verify both light and dark modes
    - _Requirements: REQ-6.3_
  
  - [ ]* 8.4 Write accessibility property tests
    - **Property 7: Focus Visibility**
    - **Property 8: Color Contrast Compliance**
    - **Property 9: Form Label Association**
    - **Property 10: Modal Focus Trapping**
    - **Validates: Requirements 6.1, 6.3, 6.4, 6.5**

- [ ] 9. Keyboard Navigation Enhancement
  - [ ] 9.1 Implement keyboard shortcuts system
    - Create `resources/js/keyboard-shortcuts.js`
    - Implement global shortcuts (Ctrl+K search, Ctrl+S save, Ctrl+N new)
    - Create keyboard shortcuts help page at `/keyboard-shortcuts`
    - _Requirements: REQ-7.1, REQ-7.5_
  
  - [ ] 9.2 Implement list navigation
    - Create `resources/js/list-navigation.js`
    - Support arrow keys, Enter, Home/End
    - Add visual focus indicator
    - _Requirements: REQ-7.2_
  
  - [ ] 9.3 Implement ARIA widget patterns
    - Add proper roles for tabs, dropdowns, autocomplete
    - Implement type-ahead for dropdowns
    - Add arrow key navigation for date pickers
    - _Requirements: REQ-7.4_
  
  - [ ]* 9.4 Write keyboard navigation tests
    - **Property 12: Keyboard Navigation Completeness**
    - **Validates: Requirements 7.1, 7.2, 7.3**

- [ ] 10. Screen Reader Support
  - [ ] 10.1 Implement ARIA live regions
    - Create `resources/views/components/accessible/live-region.blade.php`
    - Add aria-live="polite" for status messages
    - Add aria-live="assertive" for errors
    - _Requirements: REQ-8.1_
  
  - [ ] 10.2 Add semantic landmarks and labels
    - Add aria-labelledby for form groups
    - Add aria-describedby for help text
    - Add aria-expanded for collapsibles
    - _Requirements: REQ-8.2, REQ-8.3_
  
  - [ ] 10.3 Implement state announcements
    - Add aria-invalid for form errors
    - Add aria-busy for loading states
    - Add aria-pressed for toggle buttons
    - _Requirements: REQ-8.4_
  
  - [ ]* 10.4 Write screen reader tests
    - **Property 11: ARIA Live Region Announcements**
    - **Validates: Requirements 8.1**

- [ ] 11. Checkpoint - Accessibility Complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 12. PWA Enhanced Offline Functionality
  - [ ] 12.1 Implement service worker with Workbox
    - Create `public/sw.js` with caching strategies
    - Implement network-first for HTML pages
    - Implement cache-first for static assets
    - Implement stale-while-revalidate for API calls
    - _Requirements: REQ-9.1_
  
  - [ ] 12.2 Implement IndexedDB offline storage
    - Create `resources/js/offline-storage.js`
    - Implement pending_operations store
    - Implement cached_data store
    - Add operation queuing for offline changes
    - _Requirements: REQ-9.2_
  
  - [ ] 12.3 Implement background sync
    - Create `resources/js/background-sync.js`
    - Implement FIFO sync order
    - Add retry with exponential backoff (1s, 2s, 4s)
    - Create sync progress notifications
    - _Requirements: REQ-9.3_
  
  - [ ] 12.4 Implement conflict resolution
    - Create `app/Services/Offline/OfflineSyncService.php`
    - Detect conflicts (same record modified offline and online)
    - Create conflict resolution modal UI
    - _Requirements: REQ-9.4_
  
  - [ ]* 12.5 Write offline functionality tests
    - **Property 13: Offline Data Persistence**
    - **Property 14: Sync Order Preservation**
    - **Property 15: Conflict Detection Accuracy**
    - **Validates: Requirements 9.2, 9.3, 9.4**

- [ ] 13. Push Notifications
  - [ ] 13.1 Implement push notification service
    - Create `app/Services/Notifications/PushNotificationService.php`
    - Generate VAPID keys
    - Create push_subscriptions migration
    - _Requirements: REQ-10.3_
  
  - [ ] 13.2 Implement client-side push subscription
    - Create `resources/js/push-notifications.js`
    - Implement subscribe/unsubscribe flows
    - Add service worker push event handler
    - _Requirements: REQ-10.1, REQ-10.2_
  
  - [ ] 13.3 Implement notification preferences
    - Create notification settings page at `/settings/notifications`
    - Support notification types (race reminders, training alerts)
    - Implement quiet hours (default 10 PM - 8 AM)
    - _Requirements: REQ-10.4, REQ-10.5_
  
  - [ ]* 13.4 Write push notification tests
    - **Property 16: Push Notification Preference Respect**
    - **Validates: Requirements 10.4**

- [ ] 14. PWA Install Experience
  - [ ] 14.1 Implement custom install prompt
    - Create `resources/js/install-prompt.js`
    - Track engagement metrics (visits, time spent, pages viewed)
    - Show prompt after criteria met (2+ visits, 5+ minutes, 3+ pages)
    - _Requirements: REQ-11.1_
  
  - [ ] 14.2 Configure web manifest
    - Update `public/manifest.json` with icons and theme
    - Add app shortcuts (New Career, Dashboard)
    - Configure display mode as standalone
    - _Requirements: REQ-11.2, REQ-11.3_
  
  - [ ] 14.3 Implement PWA analytics
    - Create `app/Services/Analytics/PWAAnalyticsService.php`
    - Track install rate, launch count, retention
    - Create PWA metrics dashboard at `/admin/pwa-stats`
    - _Requirements: REQ-11.4_
  
  - [ ] 14.4 Implement app update flow
    - Detect service worker updates
    - Show "Update available" notification
    - Implement automatic update after 24 hours
    - _Requirements: REQ-11.5_

- [ ] 15. Checkpoint - PWA Complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 16. Batch Simulation System
  - [ ] 16.1 Create batch simulation service
    - Create `app/Services/Simulation/BatchSimulationService.php`
    - Create `app/Services/Simulation/SimulationEngine.php`
    - Support 2-10 scenarios per batch
    - _Requirements: REQ-12.1_
  
  - [ ] 16.2 Implement parallel execution
    - Create `app/Jobs/RunSimulationScenarioJob.php`
    - Configure queue priority for simulations
    - Track progress (X of Y complete)
    - _Requirements: REQ-12.2_
  
  - [ ] 16.3 Create comparison report generator
    - Create `app/Services/Simulation/ComparisonReportService.php`
    - Generate stat comparison, win rate analysis, SP efficiency
    - Highlight recommended approach
    - _Requirements: REQ-12.3, REQ-12.4_
  
  - [ ] 16.4 Create batch simulation UI
    - Create simulation wizard component
    - Create results comparison dashboard
    - Add export to PDF/Excel
    - _Requirements: REQ-12.5_
  
  - [ ]* 16.5 Write batch simulation tests
    - **Property 23: Batch Simulation Parallelism**
    - **Property 24: Comparison Report Completeness**
    - **Validates: Requirements 12.2, 12.3**

- [ ] 17. AI Model Retraining
  - [ ] 17.1 Implement prediction recording
    - Create training_predictions table migration
    - Record predictions with confidence scores
    - Link predictions to actual results
    - _Requirements: REQ-13.1_
  
  - [ ] 17.2 Implement accuracy calculation
    - Calculate RMSE, MAE, MAPE metrics
    - Track accuracy by character type
    - Create accuracy trends dashboard
    - _Requirements: REQ-13.2_
  
  - [ ] 17.3 Implement model retraining pipeline
    - Create `app/Services/AI/ModelRetrainingService.php`
    - Create `app/Jobs/RetrainPredictionModelJob.php`
    - Trigger retraining at 1000+ predictions
    - _Requirements: REQ-13.3_
  
  - [ ] 17.4 Implement A/B testing for models
    - Create `app/Services/AI/ABTestingService.php`
    - Route 50% traffic to new model
    - Implement automatic rollback if accuracy drops >2%
    - _Requirements: REQ-13.4_
  
  - [ ]* 17.5 Write AI retraining tests
    - **Property 21: Prediction Recording Completeness**
    - **Property 22: Accuracy Metric Calculation**
    - **Validates: Requirements 13.1, 13.2**

- [ ] 18. Advanced Analytics
  - [ ] 18.1 Implement pattern recognition
    - Create `app/Services/Analytics/PatternRecognitionService.php`
    - Implement K-means clustering for career analysis
    - Implement association rule mining
    - _Requirements: REQ-14.1_
  
  - [ ] 18.2 Implement career comparison
    - Create `app/Services/Analytics/CareerComparisonService.php`
    - Generate parallel coordinates charts
    - Identify divergence points
    - _Requirements: REQ-14.2_
  
  - [ ] 18.3 Create analytics visualizations
    - Create `resources/js/charts/comparison-charts.js` with D3.js
    - Implement interactive charts
    - Add trend visualization
    - _Requirements: REQ-14.3, REQ-14.4_
  
  - [ ] 18.4 Create analytics dashboard
    - Create patterns dashboard at `/analytics/patterns`
    - Add export to PDF/Excel
    - _Requirements: REQ-14.5_

- [ ] 19. Enhanced Export Formats
  - [ ] 19.1 Implement PDF export
    - Create `app/Services/Export/PDFExportService.php`
    - Create PDF template with charts
    - Add cover page with character summary
    - _Requirements: REQ-15.1_
  
  - [ ] 19.2 Implement Excel export
    - Create `app/Services/Export/ExcelExportService.php`
    - Create multiple sheets (Overview, Stats, Skills, Races)
    - Add embedded formulas and charts
    - _Requirements: REQ-15.2_
  
  - [ ] 19.3 Implement shareable links
    - Create `app/Services/Share/ShareLinkService.php`
    - Support privacy options (public, unlisted, password)
    - Add expiration dates and view tracking
    - _Requirements: REQ-15.3, REQ-15.4_
  
  - [ ]* 19.4 Write export tests
    - **Property 17: Import-Export Round-Trip**
    - **Property 18: Export Format Completeness**
    - **Property 19: Share Link Security**
    - **Validates: Requirements 15.1, 15.2, 15.3**

- [ ] 20. Checkpoint - Advanced Features Complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 21. Property-Based Testing Implementation
  - [ ] 21.1 Set up property testing framework
    - Configure Pest v4 property testing
    - Create custom generators for domain objects
    - Configure minimum 100 iterations
    - _Requirements: REQ-16.4, REQ-16.5_
  
  - [ ] 21.2 Implement stat calculation property tests
    - Create `tests/Property/StatCalculationPropertyTest.php`
    - Test stat bounds (0-1200)
    - Test calculation determinism
    - _Requirements: REQ-16.1_
  
  - [ ] 21.3 Implement data transformation property tests
    - Create `tests/Property/DataTransformationPropertyTest.php`
    - Test import-export round-trip
    - Test JSON serialization idempotence
    - _Requirements: REQ-16.2_
  
  - [ ] 21.4 Implement edge case generators
    - Create boundary value generators
    - Create invalid input generators
    - Test unicode handling
    - _Requirements: REQ-16.3_
  
  - [ ]* 21.5 Write property test for stat bounds
    - **Property 4: Stat Value Bounds**
    - **Validates: Requirements 16.1**

- [ ] 22. Browser Testing Suite
  - [ ] 22.1 Configure Playwright for cross-browser testing
    - Create `playwright.config.ts`
    - Configure Chrome, Firefox, Safari, Edge projects
    - Configure mobile viewports
    - _Requirements: REQ-17.1_
  
  - [ ] 22.2 Implement critical flow E2E tests
    - Create character creation test
    - Create training session test
    - Create skill acquisition test
    - Create race strategy test
    - _Requirements: REQ-17.1_
  
  - [ ] 22.3 Implement visual regression tests
    - Configure screenshot comparison
    - Test responsive layouts at multiple viewports
    - Test dark mode
    - _Requirements: REQ-17.2_
  
  - [ ] 22.4 Implement interaction tests
    - Test click, form, drag-and-drop events
    - Test keyboard navigation
    - Test touch events for mobile
    - _Requirements: REQ-17.3_
  
  - [ ]* 22.5 Write browser performance tests
    - Test page load times across browsers
    - Test JavaScript execution time
    - **Validates: Requirements 17.4**

- [ ] 23. Performance Benchmarking Suite
  - [ ] 23.1 Create benchmark test suite
    - Create `tests/Performance/BenchmarkSuite.php`
    - Measure page load, API response, database query times
    - Store results with git commit SHA
    - _Requirements: REQ-18.1_
  
  - [ ] 23.2 Implement CI performance gates
    - Configure threshold checks (10-20% regression)
    - Fail build on performance regression
    - Generate performance reports
    - _Requirements: REQ-18.2_
  
  - [ ] 23.3 Implement load testing
    - Create k6 load test script
    - Test 10, 50, 100, 200 concurrent users
    - Measure p50, p95, p99 response times
    - _Requirements: REQ-18.3_
  
  - [ ] 23.4 Create performance trends dashboard
    - Store benchmark history
    - Visualize performance over time
    - Create dashboard at `/admin/performance-trends`
    - _Requirements: REQ-18.5_
  
  - [ ]* 23.5 Write performance regression tests
    - **Property 30: API Response Time Bounds**
    - **Property 31: Performance Regression Detection**
    - **Validates: Requirements 18.1, 18.2**

- [ ] 24. Checkpoint - Testing Complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 25. Security Audit Implementation
  - [ ] 25.1 Configure automated security scanning
    - Set up daily `composer audit` and `npm audit`
    - Configure Dependabot for security updates
    - Create security scan GitHub Action
    - _Requirements: REQ-19.1_
  
  - [ ] 25.2 Implement security test suite
    - Create `tests/Security/VulnerabilityScanTest.php`
    - Test SQL injection protection
    - Test XSS protection
    - Test CSRF protection
    - Test authorization bypass
    - _Requirements: REQ-19.2_
  
  - [ ] 25.3 Configure static analysis
    - Configure Larastan for Laravel-specific issues
    - Configure ESLint security plugin
    - Add hardcoded secret detection
    - _Requirements: REQ-19.4_
  
  - [ ] 25.4 Create security documentation
    - Create `public/.well-known/security.txt`
    - Document vulnerability disclosure policy
    - Create security policy documentation
    - _Requirements: REQ-19.5_
  
  - [ ]* 25.5 Write security property tests
    - **Property 25: SQL Injection Prevention**
    - **Property 26: XSS Prevention**
    - **Property 27: Authorization Enforcement**
    - **Property 28: CSRF Protection**
    - **Validates: Requirements 19.2**

- [ ] 26. Privacy Controls
  - [ ] 26.1 Create privacy dashboard
    - Create `resources/views/privacy/dashboard.blade.php`
    - Add data export request functionality
    - Add account deletion request functionality
    - _Requirements: REQ-20.1_
  
  - [ ] 26.2 Implement data export service
    - Create `app/Services/Privacy/DataExportService.php`
    - Generate complete JSON export within 72 hours
    - Include all personal data with documentation
    - _Requirements: REQ-20.2_
  
  - [ ] 26.3 Implement data deletion service
    - Create `app/Services/Privacy/DataDeletionService.php`
    - Implement 30-day grace period
    - Send deletion confirmation email
    - _Requirements: REQ-20.3_
  
  - [ ] 26.4 Implement consent management
    - Create granular sharing controls
    - Track sharing activities
    - Allow consent revocation
    - _Requirements: REQ-20.4_
  
  - [ ]* 26.5 Write privacy tests
    - **Property 20: Data Deletion Completeness**
    - **Validates: Requirements 20.3**

- [ ] 27. Final Checkpoint - All Features Complete
  - Ensure all tests pass, ask the user if questions arise.
  - Run full test suite including property tests
  - Run accessibility audit
  - Run security scan
  - Verify all requirements are implemented

---

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties (minimum 100 iterations each)
- Unit tests validate specific examples and edge cases
- All property tests must include the tag format: **Feature: umamusume-career-planner-main-v2.1.0, Property {number}: {property_text}**
