# Larastan Level 9 Error Resolution Plan

**Date**: January 27, 2026  
**Total Errors**: 306 (down from 343 in previous run)  
**Status**: Ready for Implementation

## Executive Summary

This document outlines a systematic approach to resolve all 306 remaining Larastan level 9 static analysis errors. The
errors have been categorized by type and file, with a risk-based implementation order.

## Error Categories

### 1. Type Safety Issues (~120 errors)

- Mixed type casting (cast.string, cast.int, cast.double)
- Mixed type in operations (binaryOp.invalid)
- Mixed type in function parameters (argument.type)
- Missing array value types (missingType.iterableValue)

### 2. Redundant Code (~60 errors)

- Redundant is_array() checks (function.alreadyNarrowedType)
- Unnecessary null coalescing (??) operators
- Unused @phpstan-ignore comments

### 3. Undefined Variables/Properties (~40 errors)

- Undefined variables (variable.undefined)
- Undefined properties (property.notFound)
- PHPDoc parameter mismatches (parameter.notFound)

### 4. Return Type Mismatches (~35 errors)

- Method return types don't match actual returns
- Array shape mismatches

### 5. Offset Access Issues (~30 errors)

- Cannot access offset on mixed
- Invalid array key types

### 6. Method/Function Issues (~15 errors)

- Wrong parameter counts
- Method not found
- Foreach on non-iterable

### 7. Logic Issues (~6 errors)

- Always true/false conditions

## Files by Error Count

| File                         | Errors | Risk Level |
| ---------------------------- | ------ | ---------- |
| HybridAIService.php          | 73     | HIGH       |
| BenchmarkingService.php      | 67     | MEDIUM     |
| BackupService.php            | 56     | LOW        |
| MCP Services (combined)      | 45     | MEDIUM     |
| Skill Services (combined)    | 47     | MEDIUM     |
| Training Services (combined) | 20     | MEDIUM     |
| Tests & Controllers          | 10     | LOW        |

## Implementation Order (Risk-Based)

### Phase 1: Quick Wins (Low Risk, High Error Count)

**Estimated Time**: 30 minutes

#### 1.1 BackupService.php (56 errors)

**Fixes**:

- Remove 22 redundant `is_array()` checks (lines 394, 404, 406, 589, 777, 812, 837, 992, 993, 1122, 1138, 1139, 1170,
1212, 1217, 1232, 1233, 1239, 1240, 1276, 1300, 1308)
- Remove 8 unused `@phpstan-ignore` comments (lines 781, 793, 841, 861, 897, 915, 951, 967)
- Remove 6 unnecessary `??` operators (lines 810, 866, 920, 972, 1448, 1530)
- Cast 8 mixed values before string interpolation (lines 786, 813, 846, 869, 900, 923, 954, 975)
- Add type hints for 6 mixed parameters (lines 408, 430×2, 1013×2, 1140×2, 1309, 1310)
- Fix 4 binary operations with mixed (lines 1235×2, 1241×2, 1243)
- Add array value type to parameter (line 568)
- Fix return type mismatch (line 1245)

#### 1.2 Tests & Controllers (2 errors)

**Fixes**:

- Remove unused ignore comment in `ApiEndpointCoverageTest.php` (line 15)
- Fix string concatenation in `UmapyoiLiveApiTest.php` (line 201)

### Phase 2: Infrastructure Services (Medium Risk)

**Estimated Time**: 45 minutes

#### 2.1 MCP/AgentCommunicationService.php (26 errors)

**Fixes**:

- Add `array<string, mixed>` types to 8 parameters/returns (lines 41, 87, 115, 216, 274, 316, 329, 334, 347)
- Add type guards before 18 offset accesses on mixed (lines 247×3, 248×2, 249, 286×2, 290×4, 291, 292)

#### 2.2 MCP/AgentOrchestrationService.php (1 error)

**Fixes**:

- Fix return type for `getAgentStatuses()` method (line 1018)

#### 2.3 MCP/Tools/Context7Service.php (1 error)

**Fixes**:

- Adjust return type to accept `int|string` keys (line 154)

#### 2.4 MCP/Tools/FetchService.php (16 errors)

**Fixes**:

- Initialize statistics array with proper int types
- Add type assertions for 14 cache operations on mixed (lines 377, 378, 382-385, 402, 405, 407, 411, 414)

#### 2.5 BenchmarkingService.php (67 errors)

**Fixes**:

- Add array shape documentation to 12 return types (lines 72×3, 107, 166×3, 274×4, 331, 409, 662×2, 709, 730, 783)
- Add type guards before 25 offset accesses on mixed (lines 411×3, 412×3, 413×3, 417×2, 418×2, 432, 438, 444, 559, 566,
573, 622, 628, 633, 634, 635, 856×3, 863-867)
- Cast values before 15 binary operations
- Fix 10 return type mismatches (float|int vs int)
- Add type assertions before 5 function calls (lines 472, 477, 482, 622, 628, 634, 635)

### Phase 3: Domain Services (Medium Risk, Business Logic)

**Estimated Time**: 60 minutes

#### 3.1 SkillEvolutionService.php (18 errors)

**Fixes**:

- Remove 7 unused ignore comments (lines 41, 78, 84, 124, 131, 202, 230, 238, 335)
- Replace unnecessary nullsafe operator (line 147)
- Cast mixed before string interpolation (line 222)
- Remove 3 unnecessary `??` operators (lines 372, 377, 382)
- Add type guards for offset access (lines 376, 496×2, 497)
- Fix array_filter callback type (line 491)
- Fix property access on mixed (lines 531, 532)

#### 3.2 SkillHintService.php (10 errors)

**Fixes**:

- Remove 3 unused ignore comments (lines 85, 99, 233)
- Fix return type shapes (lines 153, 162)
- Add type guards for property access (lines 244, 245)
- Add null check before strtolower (line 282)
- Fix mixed parameter to strtolower (line 331)
- Remove redundant `??` operators (line 409×2)
- Remove unused ignore comment (line 436)

#### 3.3 SkillService.php (5 errors)

**Fixes**:

- Add null checks before strtolower operations
- Remove redundant null coalescing

#### 3.4 SupportCardEnrichmentService.php (14 errors)

**Fixes**:

- Add type validation for external data before offset access (lines 110, 113, 116, 119, 127×2, 139, 141×2, 142, 150,
155×2, 157, 158)

#### 3.5 TrainingPredictionService.php (6 errors)

**Fixes**:

- Add type guards before foreach (line 191)
- Validate array keys (lines 195, 197)
- Add type guards for offset access (lines 224, 258, 263)

#### 3.6 TrainingService.php (7 errors)

**Fixes**:

- Add type assertion for array_column (line 49)
- Add type guard for parameter (line 63)
- Add type guards before foreach (line 116)
- Add type guards for offset access (lines 117, 118, 125, 126)

#### 3.7 Training/BondProgressionService.php (2 errors)

**Fixes**:

- Fix undefined properties on SupportCard model (lines 73×2 or 76×2)
- Add cast for mixed to int (line 128)

#### 3.8 Training/SkillHintService.php (1 error)

**Fixes**:

- Add null check for nullable int parameter (line 69)

#### 3.9 Training/SupportBonusCalculator.php (2 errors)

**Fixes**:

- Add type hint to $card parameter (line 106)
- Cast mixed before division (line 141)

### Phase 4: Critical AI Service (High Risk, Complex)

**Estimated Time**: 45 minutes

#### 4.1 HybridAIService.php (73 errors)

**Priority**: HIGHEST - Contains undefined variables (actual bugs)

**Fixes**:

1. **Config Casting** (lines 54-57): Add proper validation/casting for config values
2. **Method Signatures** (lines 93-94, 311): Fix parameter counts
3. **Undefined Variables** (lines 357, 360×3, 366×3, 406, 409×3, 415×3, 530, 531, 594, 595): **CRITICAL** - Fix logic
bugs
4. **PHPDoc Mismatches** (lines 351×2, 400×2, 526, 590): Update PHPDoc to match actual parameters
5. **Return Types** (lines 110, 130, 132, 187, 188, 504, 568, 791×3, 794): Fix return type declarations
6. **Redundant Checks** (lines 331, 535, 544, 599, 608): Remove redundant is_array()
7. **Anonymous Classes** (lines 504, 568): Fix return type for anonymous classes
8. **Model Property** (line 798): Fix AIConversation model property access
9. **Match Expression** (line 477): Fix always-true match arm

## Fix Patterns

### Pattern 1: Redundant is_array() Check

```php
// Before
if (is_array($data)) {
    foreach ($data as $item) { ... }
}

// After (when $data is already typed as array)
foreach ($data as $item) { ... }
```text

### Pattern 2: Mixed in String Interpolation

```php
// Before
$message = "Name: $mixedVar";

// After
$message = "Name: " . (string)$mixedVar;
// or
$message = "Name: " . ($mixedVar['name'] ?? 'unknown');
```

### Pattern 3: Unnecessary Null Coalescing

```php
// Before
$value = $definitelySetVar ?? 'default';

// After
$value = $definitelySetVar;
```text

### Pattern 4: Missing Array Value Types

```php
// Before
public function getData(): array { ... }

// After
/**
 * @return array<string, mixed>
 */
public function getData(): array { ... }
```

### Pattern 5: Offset Access on Mixed

```php
// Before
$value = $mixedData['key'];

// After
if (is_array($mixedData) && isset($mixedData['key'])) {
    $value = $mixedData['key'];
}
```text

### Pattern 6: Binary Operation on Mixed

```php
// Before
$result = $mixedValue / 100;

// After
$result = (float)$mixedValue / 100;
// or
$result = is_numeric($mixedValue) ? $mixedValue / 100 : 0;
```

## Testing Strategy

### After Each File

1. Run PHPStan on specific file: `vendor/bin/phpstan analyse app/Services/[File].php --level=9`
2. Run relevant tests: `php artisan test --filter=[ServiceName]`
3. Check for regressions

### After Each Phase

1. Run full PHPStan: `vendor/bin/phpstan analyse --level=9`
2. Run full test suite: `php artisan test`
3. Document progress

### Final Validation

1. Full PHPStan level 9 analysis: `vendor/bin/phpstan analyse --level=9`
2. Full test suite: `php artisan test --coverage`
3. Manual smoke testing of critical features

## Risk Mitigation

### High-Risk Changes

- **HybridAIService undefined variables**: Examine code context carefully, may need to refactor logic
- **BenchmarkingService calculations**: Verify math operations still produce correct results
- **Model property access**: Check if properties exist or need accessors

### Rollback Plan

- Git commit after each phase
- Keep original error report for reference
- Document any behavioral changes

## Success Criteria

- [ ] All 306 errors resolved
- [ ] No new errors introduced
- [ ] All tests passing
- [ ] PHPStan level 9 clean
- [ ] No behavioral changes (unless documented)

## Implementation Notes

### BackupService.php Specific Patterns Found

The file has these specific anti-patterns that need fixing:

1. **Redundant nested is_array() checks** (lines 394, 404, 406):

```php
// Current (WRONG):
if (((is_array($backupRecord) && isset($backupRecord['user_id']) ? $backupRecord['user_id'] : null)) !== $userId)

// Should be:
if (($backupRecord['user_id'] ?? null) !== $userId)
```text

1. **Complex nested type checks** (lines 404, 406):

```php
// Current (WRONG):
$filePath = \is_string((is_array($backupRecord) && isset($backupRecord['file_path']) ? $backupRecord['file_path'] : null)) ? $backupRecord['file_path'] : '';

// Should be:
$filePath = is_string($backupRecord['file_path'] ?? null) ? $backupRecord['file_path'] : '';
```

1. **Unnecessary ?? on variables that always exist** (line 810):

```php
// Current (WRONG):
$restored = ($restored ?? 0) + 1;

// Should be:
$restored = $restored + 1;
```text

1. **Mixed in string interpolation** (lines 786, 813, 846, etc.):

```php
// Current (WRONG):
$warnings[] = "Character '{$charName}' already exists, skipped";
// where $charName comes from mixed type

// Should be:
$charName = is_string($charData['name'] ?? null) ? $charData['name'] : 'Unknown';
$warnings[] = "Character '" . $charName . "' already exists, skipped";
```

## Progress Tracking

| Phase     | Files                        | Errors  | Status                | Time    |
| --------- | ---------------------------- | ------- | --------------------- | ------- |
| 1.1       | BackupService                | 56      | ✅ Complete (0 errors)| 30min   |
| 1.2       | Tests                        | 2       | ✅ Complete (0 errors)| 5min    |
| 2.1       | AgentCommunicationService    | 27      | ✅ Complete (0 errors)| 15min   |
| 2.2       | AgentOrchestrationService    | 1       | ✅ Complete (0 errors)| 5min    |
| 2.3       | Context7Service              | 1       | ✅ Complete (0 errors)| 5min    |
| 2.4       | FetchService                 | 18      | ✅ Complete (0 errors)| 10min   |
| 2.5       | BenchmarkingService          | 70      | ✅ Complete           | 45min   |
| 3.1       | SkillEvolutionService        | 18      | ✅ Complete           | 20min   |
| 3.2       | SkillHintService             | 10      | ✅ Complete           | 15min   |
| 3.3       | SkillService                 | 5       | ✅ No errors found    | -       |
| 3.4       | SupportCardEnrichmentService | 14      | ✅ Complete           | 10min   |
| 3.5       | TrainingPredictionService    | 6       | ✅ Complete           | 10min   |
| 3.6       | TrainingService              | 7       | ✅ Complete           | 10min   |
| 3.7       | BondProgressionService       | 3       | ✅ Complete           | 5min    |
| 3.8       | Training/SkillHintService    | 1       | ✅ Complete           | 5min    |
| 3.9       | SupportBonusCalculator       | 2       | ✅ Complete           | 5min    |
| 4.1       | HybridAIService              | 73      | ✅ Complete           | 45min   |
| **TOTAL** | **16 files**                 | **306** | **✅ 100% Complete**  | **~4h** |

## Completed Fixes Summary

### Phase 1.1: BackupService.php ✅

- Fixed all 56 errors
- Removed redundant is_array() checks
- Added proper type annotations
- Fixed mixed type operations
- Verified: 0 errors remaining

### Phase 1.2: Test Files ✅

- `ApiEndpointCoverageTest.php`: Removed unused @phpstan-ignore comment
- `UmapyoiLiveApiTest.php`: Cast config value to string before concatenation
- Verified: 0 errors remaining

### Phase 2.1: AgentCommunicationService.php ✅

- Added PHPDoc type annotations for all array parameters/returns
- Fixed Cache::get() return type handling with proper type guards
- Fixed usort callback with proper type assertions
- Verified: 0 errors remaining

### Phase 2.2: AgentOrchestrationService.php ✅

- Fixed getAgentStatuses() return type with proper cache type handling
- Verified: 0 errors remaining

### Phase 2.3: Context7Service.php ✅

- Fixed retrieveContext() return type with proper array key typing
- Cast age value to int
- Verified: 0 errors remaining

### Phase 2.4: FetchService.php ✅

- Rewrote getStatistics() with proper type extraction from cache
- Rewrote trackStatistics() with proper type handling
- Verified: 0 errors remaining

### Phase 2.5: BenchmarkingService.php ✅

- Fixed ~70 errors
- Simplified return type annotations to use `array<string, mixed>`
- Added proper type guards for benchmark comparisons
- Fixed generatePerformanceSummary() and identifyImprovementAreas()
- Fixed analyzeBenchmarkTrends() with proper cache handling
- Fixed groupCareersByMonth() with Carbon type check
- Fixed calculateCareerEfficiency() with proper property access
- Verified: 0 errors remaining

### Phase 3: Domain Services ✅

- SkillEvolutionService: Fixed 18 errors (removed duplicate method, fixed type issues)
- SkillHintService: Fixed 10 errors (removed redundant checks, fixed sorting)
- SupportCardEnrichmentService: Fixed 14 errors (rewrote applyEnrichment)
- TrainingPredictionService: Fixed 6 errors
- TrainingService: Fixed 7 errors (fixed bond updates type)
- BondProgressionService: Fixed 3 errors (property access)
- Training/SkillHintService: Fixed 1 error (nullable int)
- SupportBonusCalculator: Fixed 2 errors
- SPBudgetManagementAgent: Fixed 1 error (redundant is_object)
- Verified: 0 errors remaining

### Phase 4.1: HybridAIService.php ✅

- Fixed all 73 errors
- **Critical bug fixes**: Fixed undefined variables ($context, $prompt, $complexity) in processWithMCPStrands() and
processWithMCPAgentCore() - these methods were missing their parameters
- Extracted anonymous classes to named wrapper classes (StrandsAgentWrapper, AgentCoreWrapper) to fix return type issues
- Fixed config value casting with proper type guards
- Fixed method invocations (processWithMCPStrands/processWithMCPAgentCore now accept proper parameters)
- Fixed PHPDoc parameter references
- Fixed match expression always-true arm
- Fixed getConversationHistory to use ConversationMessage model instead of AIConversation
- Fixed storeConversation to properly create conversation and messages
- Removed redundant is_array() checks
- Verified: 0 errors remaining

## Additional Fixes During Verification

- `SPBudgetManagementAgent.php`: Removed redundant is_object() check
- `SkillHintService.php`: Fixed redundant is_array() and simplified priority sorting
- `TrainingService.php`: Fixed bondUpdates array type annotation

---

**Document Version**: 1.2  
**Last Updated**: January 27, 2026  
**Status**: ✅ COMPLETE - All 306 errors resolved
