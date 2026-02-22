# Larastan Level 9 Fix Strategy

**Version**: 1.0.0  
**Date**: 2026-01-23  
**Total Errors**: ~1,200+ errors across 317 error instances  
**Status**: Ready for Implementation  

---

## Table of Contents

1. [Error Types Summary](#error-types-summary)
2. [Categorized Errors](#categorized-errors)
3. [Fix Dependencies](#fix-dependencies)
4. [Execution Plan](#execution-plan)
5. [Batch Implementation Strategy](#batch-implementation-strategy)

---

## Error Types Summary

### Top Error Categories (by frequency)

1. **missingType** errors (~350+): Missing type specifications on parameters, return types, and properties
2. **property.notFound** errors (~120+): Accessing undefined properties on models
3. **offsetAccess.nonOffsetAccessible** errors (~200+): Accessing offsets on mixed types
4. **cast** errors (~150+): Cannot cast mixed to int/float/string
5. **argument.type** errors (~120+): Type mismatches in function/method arguments
6. **return.type** errors (~60+): Return type mismatches
7. **missingType.generics** errors (~50+): Missing generic type specifications
8. **binaryOp.invalid** errors (~80+): Binary operations with mixed types
9. **property.nonObject** errors (~80+): Accessing properties on potentially null objects
10. **method.notFound** errors (~15+): Calling undefined methods
11. **foreach.nonIterable** errors (~25+): Using foreach on non-iterable types
12. **property.onlyWritten** errors (~8): Properties never read

---

## Categorized Errors

### CATEGORY 1: Missing Type Declarations (Priority: HIGH)

**Impact**: Foundation for all other fixes  
**Error Types**: `missingType.return`, `missingType.parameter`, `missingType.iterableValue`, `missingType.generics`

#### Files Affected (High Priority)

**Models** (50+ instances):

- `app/Models/AIConversation.php` (Lines: 178, 186, 194, scope methods)
- `app/Models/Character.php` (Lines: 204, 212, scope methods)
- `app/Models/ConversationMessage.php` (Lines: 177-249, multiple scope methods)
- `app/Models/ExternalData.php` (Lines: 47, 56, 61, scope methods)
- `app/Models/MCPAgent.php` (Lines: 108, 156, 164, 172, + relationships)
- `app/Models/MCPServer.php` (Lines: 191, 235, 253, 352, 395-427)
- `app/Models/MCPToolUsage.php` (Lines: 118-226, relationships and scopes)
- `app/Models/OCRExtraction.php` (Lines: 42, 47, 52, 57, scopes)
- `app/Models/UserPreference.php` (Lines: 266-322, all scopes)
- `app/Models/SkillAcquisition.php` (Line: 119, attribute casts)

**Controllers** (40+ instances):

- `app/Http/Controllers/DataManagementController.php` (Line: 265)
- `app/Http/Controllers/SkillController.php` (Line: 14)

**Services** (200+ instances):

- `app/Services/AI/AgentFeedbackService.php` (Lines: 30, 72, 106, 155, 201, 248, 279, 328-567)
- `app/Services/AI/Agents/AgentOrchestrationService.php` (Multiple methods)
- `app/Services/Agents/HintOptimizationAgent.php` (Lines: 30-452, all methods)
- `app/Neuron/Agents/Tools/*` (Multiple files with array return types)

**Repositories** (4 instances):

- `app/Repositories/CharacterRepositoryInterface.php` (Lines: 19, 21)
- `app/Repositories/EloquentCharacterRepository.php` (Lines: 25, 30)

#### Fix Approach

1. **Add PHPDoc type hints** for array shapes:

   ```php
   /**
    * @param array<string, mixed> $data
    * @return array{key: string, value: int}
    */
   ```

2. **Specify generic types** for collections and relationships:

   ```php
   /** @return HasMany<TRelatedModel, TDeclaringModel> */
   public function messages(): HasMany
   ```

3. **Add return types** to all methods:

   ```php
   public function scopeActive(Builder $query): Builder
   ```

4. **Parameter types** for arrays:

   ```php
   /** @param array<string, mixed> $filters */
   public function filter(array $filters): Collection
   ```

---

### CATEGORY 2: Undefined Property Access (Priority: HIGH)

**Impact**: Runtime errors, data integrity issues  
**Error Types**: `property.notFound`, `property.nonObject`

#### Files Affected (120+ instances)

**AIConversation Model Properties** (20+ instances):

- `app/Services/AI/AIDashboardService.php` (Lines: 320-322, 373, 427)
- `app/Services/AI/ConversationHistoryService.php` (Lines: 86-90, 123-127, 176, 200)
- `app/Services/AI/HybridAIService.php` (Lines: 796-800)
- Missing properties: `ai_model_used`, `processing_time`, `cost`, `message_content`, `token_count`

**ExternalData Model**:

- `app/Models/ExternalData.php` (Line: 44) - `is_valid` property

**Skill/Character Properties** (30+ instances):

- `app/Http/Controllers/Api/V1/CharacterController.php` (Lines: 206, 220, 237) - Collection vs Model confusion
- `app/Http/Controllers/Api/V1/SkillController.php` (Lines: 177, 180-181, 190) - evolutionTarget, evolutionSource

**SupportCardResource** (18+ instances):

- `app/Http/Resources/Api/V1/SupportCardResource.php` (Lines: 18-35) - All properties undefined

**User Null Safety** (30+ instances):

- Multiple controllers accessing `Auth::user()->id` without null check
- Files: `CareerReportController.php`, `DataManagementController.php`, `ExportController.php`, etc.

#### Fix Approach

1. **Add missing properties** to models:

   ```php
   // In migration
   $table->string('ai_model_used')->nullable();
   $table->float('processing_time')->nullable();
   ```

2. **Add to $fillable and $casts**:

   ```php
   protected $fillable = [..., 'ai_model_used', 'processing_time'];
   protected $casts = ['processing_time' => 'float'];
   ```

3. **Fix Collection/Model confusion**:

   ```php
   // Use firstOrFail() or handle collections properly
   $skill = Skill::findOrFail($id); // Not Skill::where()->get()
   ```

4. **Add null safety checks**:

   ```php
   $userId = Auth::id() ?? throw new AuthenticationException();
   ```

---

### CATEGORY 3: Mixed Type Issues (Priority: HIGH)

**Impact**: Type safety violations  
**Error Types**: `offsetAccess.nonOffsetAccessible`, `cast.int`, `cast.double`, `cast.string`

#### Files Affected (350+ instances)

**Offset Access on Mixed** (200+ instances):

- `app/Jobs/SyncExternalDataJob.php` (Lines: 447-574, all cache operations)
- `app/Services/AI/AIPerformanceMonitor.php` (Lines: 50-346, all metric operations)
- `app/Http/Controllers/DataManagementController.php` (Lines: 292-307, operation status)
- `app/Services/ApiPerformanceMonitoringService.php` (Lines: 446-629, endpoint metrics)
- `app/Http/Middleware/TieredRateLimiting.php` (Lines: 119-292, config access)

**Cast Errors** (150+ instances):

- **cast.int** (~60): OCRUploadController, MCPMonitoringController, ProfileController, AI Services
- **cast.double** (~70): TrainingPredictionResource, AI Dashboard, Performance services
- **cast.string** (~20): ProfileController, BedrockService, MemoryGuardServiceProvider

#### Fix Approach

1. **Add config validation** with proper types:

   ```php
   /**  
    * @return array{requests_per_minute: int, requests_per_hour: int}
    */
   private function getTierConfig(string $tier): array
   {
       $config = config("rate_limiting.tiers.{$tier}");
       if (!is_array($config)) {
           throw new InvalidArgumentException("Invalid tier config");
       }
       return $config;
   }
   ```

2. **Validate request input** before access:

   ```php
   $validated = $request->validate([
       'field' => 'required|integer'
   ]);
   $value = $validated['field']; // Now guaranteed to be int
   ```

3. **Type assertions** for config/cache:

   ```php
   /** @var array<string, mixed> $data */
   $data = Cache::get('key', []);
   ```

---

### CATEGORY 4: Argument Type Mismatches (Priority: MEDIUM)

**Impact**: Function call failures  
**Error Types**: `argument.type`

#### Files Affected (120+ instances)

**Service Method Calls** (80+ instances):

- `app/Http/Controllers/ExportController.php` (Lines: 57, 70-78, type string vs mixed)
- `app/Http/Controllers/ImportController.php` (Lines: 75-77, 90, 136)
- `app/Http/Controllers/MigrationController.php` (Lines: 68, 82, 135, 276, 314)
- `app/Services/AI/Agents/AgentOrchestrationService.php` (Lines: 241-392, array shape mismatches)

**Function Parameter Issues** (40+ instances):

- `round()` calls with mixed (30+instances across AI services)
- `array_*` functions with mixed parameters (20+ instances)
- `sprintf()` with mixed values (ConversationHistoryService, lines 330-338)

#### Fix Approach

1. **Validate input early**:

   ```php
   public function export(Request $request): Response
   {
       $exportType = $request->validated('export_type'); // Now string, not mixed
       return $this->service->export($exportType);
   }
   ```

2. **Type casting with validation**:

   ```php
   $num = is_numeric($value) ? (float)$value : 0.0;
   $result = round($num, 2);
   ```

---

### CATEGORY 5: Return Type Mismatches (Priority: MEDIUM)

**Impact**: Contract violations  
**Error Types**: `return.type`

#### Files Affected (60+ instances)

**Service Returns** (40+ instances):

- `app/Services/AI/AIDashboardService.php` (Lines: 67, 578, 608)
- `app/Services/AI/BedrockConfigurationService.php` (Lines: 228, 264, 319)
- `app/Services/AI/CostTrackingService.php` (Lines: 112, 137, 163, 188)
- `app/Services/AI/AgentFeedbackService.php` (Line: 544, wrong return type)
- `app/Services/AI/Agents/AgentOrchestrationService.php` (Lines: 72, 138, 245, 261)

**Cache/Config Returns** (10+ instances):

- `app/Neuron/Support/McpConnectorFactory.php` (Line: 159)
- `app/Neuron/Support/McpToolIntegration.php` (Lines: 188, 216, 229, 240)

**Model Methods** (10+ instances):

- `app/Models/Skill.php` (Line: 298, array shape mismatch)

#### Fix Approach

1. **Ensure return matches declaration**:

   ```php
   /** @return array<string, float> */
   public function getCostByProvider(): array
   {
       return $costs->pluck('total', 'provider')->all(); // Cast if needed
   }
   ```

2. **Add proper type conversion**:

   ```php
   public function getConfig(): ?string
   {
       $value = config('key');
       return is_string($value) ? $value : null;
   }
   ```

---

### CATEGORY 6: Generic Type Specifications (Priority: MEDIUM)

**Impact**: Collection type safety  
**Error Types**: `missingType.generics`, `argument.templateType`

#### Files Affected (50+ instances)

**Model Traits** (25+ instances):

- Multiple models missing `@template TFactory` specification
- Missing relationship generic types (HasMany, BelongsTo)

**Collection Parameters** (20+ instances):

- `app/Services/AI/AgentFeedbackService.php` (Lines: 328, 342, 417, 555, 561)
- `app/Services/AI/ConversationManagementService.php` (Lines: 429, 492, 548, 571, 590, 610, 637, 655)
- `app/Services/Agents/HintOptimizationAgent.php` (Lines: 30, 84, 360, 374, 436)

**Unable to Resolve Template** (5+ instances):

- `app/Http/Requests/StoreSupportDeckRequest.php` (Lines: 60, 66)

#### Fix Approach

1. **Add Factory generics**:

   ```php
   /**
    * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
    * @use HasFactory<TFactory>
    */
   use HasFactory;
   ```

2. **Specify Collection types**:

   ```php
   /**
    * @param Collection<int, Skill> $skills
    * @return array<string, mixed>
    */
   public function analyze(Collection $skills): array
   ```

3. **Relationship generics**:

   ```php
   /** @return HasMany<Message, $this> */
   public function messages(): HasMany
   {
       return $this->hasMany(Message::class);
   }
   ```

---

### CATEGORY 7: Binary Operation Errors (Priority: LOW)

**Impact**: Mathematical operations  
**Error Types**: `binaryOp.invalid`, `assignOp.invalid`

#### Files Affected (80+ instances)

- Division/multiplication with mixed operands
- String concatenation with mixed
- Compound assignments (+=, *=, /=) with mixed

#### Fix Approach

Ensure both operands are typed before operations:

```php
$validated = (float)$value1 / (float)$value2;
```

---

### CATEGORY 8: Null Safety Issues (Priority: MEDIUM)

**Impact**: Null pointer exceptions  
**Error Types**: `property.nonObject`, `method.nonObject`

#### Files Affected (80+ instances)

- User authentication checks (30+)
- Carbon date operations (10+)
- Relationship access (40+)

#### Fix Approach

```php
$user = Auth::user();
if ($user === null) {
    throw new AuthenticationException();
}
$userId = $user->id;
```

---

### CATEGORY 9: Foreach/Iteration Errors (Priority: MEDIUM)

**Error Types**: `foreach.nonIterable`, `foreach.emptyArray`

#### Files Affected (25+ instances)

- `app/Neuron/Agents/Tools/RaceDataTool.php` (Lines: 202-366, string treated as array)
- `app/Jobs/SyncExternalDataJob.php` (Line: 540)
- Config iteration issues

#### Fix Approach

```php
$items = is_array($data) ? $data : [];
foreach ($items as $item) {
    // Safe iteration
}
```

---

### CATEGORY 10: Unused Properties (Priority: LOW)

**Error Types**: `property.onlyWritten`

#### Files Affected

- `app/Http/Controllers/DataManagementController.php` (Lines: 29-32, 4 properties)
- `app/Http/Controllers/MigrationController.php` (Line: 30, 1 property)

#### Fix Approach

Remove unused properties or actually use them in methods.

---

### CATEGORY 11: Method Not Found (Priority: HIGH)

**Error Types**: `method.notFound`

#### Files Affected

- `app/Jobs/SyncExternalDataJob.php` (Lines: 268-274, 7 undefined ExternalDataService methods)
- `app/Http/Controllers/Api/V1/SkillController.php` (Line: 139, skillAcquisitions on Collection)
- `app/Http/Controllers/PerformanceController.php` (Line: 335, getRedis on Repository)

#### Fix Approach

1. Add missing methods to services
2. Fix collection/model confusion
3. Ensure correct interface usage

---

### CATEGORY 12: PHPDoc Covariance Issues (Priority: LOW)

**Error Types**: `property.phpDocType`

#### Files Affected

- Multiple models with `array<int, string>` vs `list<string>` for $fillable

#### Fix Approach

Change PHPDoc to `@var list<string>` or suppress with stubs.

---

## Fix Dependencies

### Dependency Chain

```
1. Missing Type Declarations (CATEGORY 1)
   ↓ Enables
2. Undefined Properties (CATEGORY 2)
   ↓ Enables
3. Mixed Type Issues (CATEGORY 3)
   ↓ Enables
4. Argument Type Mismatches (CATEGORY 4)
   ↓ Enables
5. Return Type Mismatches (CATEGORY 5)
```

**Independent (Can be fixed in parallel)**:

- Generic Type Specifications (CATEGORY 6)
- Null Safety Issues (CATEGORY 8)
- Foreach/Iteration Errors (CATEGORY 9)
- Unused Properties (CATEGORY 10)
- Method Not Found (CATEGORY 11)

---

## Execution Plan

### Phase 1: Foundation (Week 1)

**Goal**: Establish type safety foundation

1. **Add missing model properties** (CATEGORY 2 - Models)
   - Create migration for AIConversation missing fields
   - Update model $fillable and $casts
   - **Estimated**: 4 hours

2. **Fix Model scope return types** (CATEGORY 1 - Models)
   - Add return types to all scope methods
   - Add generic types to relationships
   - **Estimated**: 6 hours

3. **Add Repository type hints** (CATEGORY 1 - Repositories)
   - Update interfaces and implementations
   - **Estimated**: 2 hours

### Phase 2: Controllers & Requests (Week 1-2)

**Goal**: Type-safe request handling

1. **Fix User null safety** (CATEGORY 8 - Controllers)
   - Add Auth::id() null checks across all controllers
   - **Estimated**: 4 hours

2. **Validate request inputs** (CATEGORY 3 - Controllers)
   - Ensure all mixed request->input() are validated
   - **Estimated**: 6 hours

3. **Fix Collection vs Model** (CATEGORY 2 - Controllers)
   - Review all Eloquent query results
   - **Estimated**: 3 hours

### Phase 3: Services Layer (Week 2-3)

**Goal**: Type-safe service layer

1. **Add Service method signatures** (CATEGORY 1 - Services)
   - Document all array parameters and returns
   - **Estimated**: 16 hours (100+ methods)

2. **Fix argument type mismatches** (CATEGORY 4 - Services)
   - Ensure service calls use correct types
   - **Estimated**: 8 hours

3. **Fix return type mismatches** (CATEGORY 5 - Services)
   - Align implementations with declarations
   - **Estimated**: 6 hours

4. **Add Config/Cache type safety** (CATEGORY 3 - Services)
    - Validate config access
    - Add type assertions for cache
    - **Estimated**: 8 hours

### Phase 4: AI & Agent Systems (Week 3-4)

**Goal**: Fix AI-specific type issues

1. **Fix AI Dashboard types** (CATEGORY 3, 4, 5)
    - AIDashboardService, AIPerformanceMonitor
    - **Estimated**: 6 hours

2. **Fix Agent type signatures** (CATEGORY 1, 6)
    - AgentFeedbackService, Orchestration, Hint Optimization
    - **Estimated**: 10 hours

3. **Fix Bedrock/Ollama services** (CATEGORY 3, 4)
    - Cast operations, config access
    - **Estimated**: 4 hours

### Phase 5: Specialized Components (Week 4)

**Goal**: Fix remaining specialized code

1. **Fix Neuron/MCP components** (ALL CATEGORIES)
    - McpConnectorFactory, McpToolIntegration
    - **Estimated**: 6 hours

2. **Fix Performance/APM services** (CATEGORY 3, 4, 5)
    - ApiPerformanceMonitoring, ApmService, Caching
    - **Estimated**: 8 hours

3. **Fix Jobs & Data Sync** (CATEGORY 3, 11)
    - SyncExternalDataJob missing methods
    - **Estimated**: 4 hours

### Phase 6: Cleanup & Verification (Week 4-5)

**Goal**: Polish and verify

1. **Fix unused properties** (CATEGORY 10)
    - Remove or use properties
    - **Estimated**: 1 hour

2. **Fix foreach issues** (CATEGORY 9)
    - RaceDataTool string iteration
    - **Estimated**: 2 hours

3. **Run Larastan and fix regressions**
    - **Estimated**: 4 hours

4. **Run test suite**
    - **Estimated**: 2 hours

**Total Estimated Time**: 110 hours (~3-4 weeks with 1-2 developers)

---

## Batch Implementation Strategy

### Batch 1: Core Foundation (Day 1-3)

**Priority**: CRITICAL  
**Files**: 15 models, 4 repositories  
**Focus**: Missing types, undefined properties  
**Success Metric**: All model-related errors resolved (~150 errors)

### Batch 2: Controllers (Day 4-6)

**Priority**: HIGH  
**Files**: 15 controllers  
**Focus**: User null safety, request validation  
**Success Metric**: All controller errors resolved (~120 errors)

### Batch 3: Core Services (Day 7-12)

**Priority**: HIGH  
**Files**: 20 service files  
**Focus**: Method signatures, type safety  
**Success Metric**: Non-AI services resolved (~300 errors)

### Batch 4: AI Services (Day 13-18)

**Priority**: MEDIUM  
**Files**: 15 AI service files  
**Focus**: Complex type operations, agents  
**Success Metric**: All AI errors resolved (~400 errors)

### Batch 5: Infrastructure & Jobs (Day 19-22)

**Priority**: MEDIUM  
**Files**: 10 infrastructure files  
**Focus**: APM, caching, jobs, Neuron  
**Success Metric**: All infrastructure errors resolved (~200 errors)

### Batch 6: Final Polish (Day 23-25)

**Priority**: LOW  
**Files**: All remaining  
**Focus**: Cleanup, testing, verification  
**Success Metric**: Larastan level 9 passes with 0 errors

---

## Testing Strategy

### Per-Batch Testing

1. Run Larastan on changed files only
2. Run affected Pest tests
3. Manual QA for critical paths
4. Document any breaking changes

### Final Verification

1. Full Larastan analysis: `vendor/bin/phpstan analyse --level=9`
2. Full test suite: `php artisan test`
3. Manual testing of key features
4. Performance regression check

---

## Risk Mitigation

### High-Risk Changes

1. **AIConversation property additions** - May affect existing data
   - Mitigation: Nullable fields, data migration script

2. **Config access changes** - May break runtime
   - Mitigation: Test all config-dependent features

3. **Collection/Model fixes** - May change query behavior
   - Mitigation: Review all affected queries

### Rollback Plan

- Git branch per batch
- Tag releases after each successful batch
- Keep detailed change log

---

## Success Criteria

✅ **Larastan Level 9**: 0 errors  
✅ **Test Suite**: All passing  
✅ **No Breaking Changes**: Existing functionality preserved  
✅ **Documentation**: All complex types documented  
✅ **Performance**: No degradation  

---

## Notes

- Some errors may be false positives (stubs can address these)
- Consider upgrading Larastan/PHPStan if persistent issues
- Review Laravel 12 best practices during fixes
- Coordinate with team on breaking changes

---

**END OF STRATEGY DOCUMENT**
