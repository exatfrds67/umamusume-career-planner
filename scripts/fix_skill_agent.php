<?php

/**
 * Script to fix SkillManagementAgent.php for PHPStan level 9 compliance
 */
$file = 'app/Services/AI/Agents/SkillManagementAgent.php';
$content = file_get_contents($file);

// === Fix 1: optimizeSPAllocation method body ===
$old1 = <<<'PATTERN'
            // Process through MCP agent
            $response = $this->processWithMCPAgent($context);

            $allocation = [
                'allocation' => $response['allocation'] ?? [],
                'priority_skills' => $response['priority_skills'] ?? [],
                'reasoning' => $response['reasoning'] ?? 'SP allocation optimized',
                'confidence' => $response['confidence'] ?? 0.85,
                'metadata' => [
                    'agent_id' => $this->agentId,
                    'processing_time' => microtime(true) - $startTime,
                    'character_id' => $character->id,
                ],
            ];
PATTERN;

$new1 = <<<'PATTERN'
            // Process through MCP agent
            $response = $this->processWithMCPAgent($context);

            $rawAllocation = $response['allocation'] ?? null;
            /** @var array<string, mixed> $allocationData */
            $allocationData = is_array($rawAllocation) ? $rawAllocation : [];

            $rawPrioritySkills = $response['priority_skills'] ?? null;
            /** @var array<int, array<string, mixed>> $prioritySkills */
            $prioritySkills = is_array($rawPrioritySkills) ? array_values($rawPrioritySkills) : [];

            $rawReasoning = $response['reasoning'] ?? 'SP allocation optimized';
            $reasoning = is_string($rawReasoning) ? $rawReasoning : 'SP allocation optimized';

            $rawConfidence = $response['confidence'] ?? 0.85;
            $confidence = is_numeric($rawConfidence) ? (float) $rawConfidence : 0.85;

            $allocation = [
                'allocation' => $allocationData,
                'priority_skills' => $prioritySkills,
                'reasoning' => $reasoning,
                'confidence' => $confidence,
                'metadata' => [
                    'agent_id' => $this->agentId,
                    'processing_time' => microtime(true) - $startTime,
                    'character_id' => $character->id,
                ],
            ];
PATTERN;

$content = str_replace($old1, $new1, $content);

// === Fix 2: Add cache type annotation ===
$old2 = <<<'PATTERN'
            // Check cache
            $cacheKey = $this->getCacheKey($character->id, $context);
            if ($cached = Cache::get($cacheKey)) {
                return $cached;
            }
PATTERN;

$new2 = <<<'PATTERN'
            $cacheKey = $this->getCacheKey($character->id, $context);
            /** @var array{allocation: array<string, mixed>, priority_skills: array<int, array<string, mixed>>, reasoning: string, confidence: float, metadata: array<string, mixed>}|null $cached */
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
PATTERN;

$content = str_replace($old2, $new2, $content);

// === Fix 3: Fix generateHintCollectionStrategy ===
$old3 = <<<'PATTERN'
            $response = $this->processWithMCPAgent($context);

            return [
                'strategy' => $response['strategy'] ?? [],
                'hint_sources' => $response['hint_sources'] ?? [],
                'expected_savings' => $response['expected_savings'] ?? 0,
                'confidence' => $response['confidence'] ?? 0.85,
                'reasoning' => $response['reasoning'] ?? 'Hint collection strategy generated',
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Hint strategy generation failed', [
PATTERN;

$new3 = <<<'PATTERN'
            $response = $this->processWithMCPAgent($context);

            $rawStrategy = $response['strategy'] ?? null;
            /** @var array<string, mixed> $strategy */
            $strategy = is_array($rawStrategy) ? $rawStrategy : [];

            $rawHintSources = $response['hint_sources'] ?? null;
            /** @var array<int, array<string, mixed>> $hintSources */
            $hintSources = is_array($rawHintSources) ? array_values($rawHintSources) : [];

            $rawExpectedSavings = $response['expected_savings'] ?? 0;
            $expectedSavings = is_numeric($rawExpectedSavings) ? (int) $rawExpectedSavings : 0;

            $rawConfidence = $response['confidence'] ?? 0.85;
            $confidence = is_numeric($rawConfidence) ? (float) $rawConfidence : 0.85;

            $rawReasoning = $response['reasoning'] ?? 'Hint collection strategy generated';
            $reasoning = is_string($rawReasoning) ? $rawReasoning : 'Hint collection strategy generated';

            return [
                'strategy' => $strategy,
                'hint_sources' => $hintSources,
                'expected_savings' => $expectedSavings,
                'confidence' => $confidence,
                'reasoning' => $reasoning,
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Hint strategy generation failed', [
PATTERN;

$content = str_replace($old3, $new3, $content);

// === Fix 4: Fix planSkillEvolution ===
$old4 = <<<'PATTERN'
            $response = $this->processWithMCPAgent($context);

            return [
                'evolution_plan' => $response['evolution_plan'] ?? [],
                'prerequisites' => $response['prerequisites'] ?? [],
                'total_sp_cost' => $response['total_sp_cost'] ?? 0,
                'confidence' => $response['confidence'] ?? 0.85,
                'reasoning' => $response['reasoning'] ?? 'Evolution plan created',
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill evolution planning failed', [
PATTERN;

$new4 = <<<'PATTERN'
            $response = $this->processWithMCPAgent($context);

            $rawEvolutionPlan = $response['evolution_plan'] ?? null;
            /** @var array<int, array<string, mixed>> $evolutionPlan */
            $evolutionPlan = is_array($rawEvolutionPlan) ? array_values($rawEvolutionPlan) : [];

            $rawPrerequisites = $response['prerequisites'] ?? null;
            /** @var array<int, string> $prerequisites */
            $prerequisites = is_array($rawPrerequisites) ? array_values($rawPrerequisites) : [];

            $rawTotalSpCost = $response['total_sp_cost'] ?? 0;
            $totalSpCost = is_numeric($rawTotalSpCost) ? (int) $rawTotalSpCost : 0;

            $rawConfidence = $response['confidence'] ?? 0.85;
            $confidence = is_numeric($rawConfidence) ? (float) $rawConfidence : 0.85;

            $rawReasoning = $response['reasoning'] ?? 'Evolution plan created';
            $reasoning = is_string($rawReasoning) ? $rawReasoning : 'Evolution plan created';

            return [
                'evolution_plan' => $evolutionPlan,
                'prerequisites' => $prerequisites,
                'total_sp_cost' => $totalSpCost,
                'confidence' => $confidence,
                'reasoning' => $reasoning,
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill evolution planning failed', [
PATTERN;

$content = str_replace($old4, $new4, $content);

// === Fix 5: Fix recommendSkillBuild ===
$old5 = <<<'PATTERN'
            $response = $this->processWithMCPAgent($context);

            return [
                'build' => $response['build'] ?? [],
                'core_skills' => $response['core_skills'] ?? [],
                'optional_skills' => $response['optional_skills'] ?? [],
                'total_sp_required' => $response['total_sp_required'] ?? 0,
                'confidence' => $response['confidence'] ?? 0.85,
                'reasoning' => $response['reasoning'] ?? 'Skill build recommended',
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill build recommendation failed', [
PATTERN;

$new5 = <<<'PATTERN'
            $response = $this->processWithMCPAgent($context);

            $rawBuild = $response['build'] ?? null;
            /** @var array<string, mixed> $build */
            $build = is_array($rawBuild) ? $rawBuild : [];

            $rawCoreSkills = $response['core_skills'] ?? null;
            /** @var array<int, string> $coreSkills */
            $coreSkills = is_array($rawCoreSkills) ? array_values($rawCoreSkills) : [];

            $rawOptionalSkills = $response['optional_skills'] ?? null;
            /** @var array<int, string> $optionalSkills */
            $optionalSkills = is_array($rawOptionalSkills) ? array_values($rawOptionalSkills) : [];

            $rawTotalSpRequired = $response['total_sp_required'] ?? 0;
            $totalSpRequired = is_numeric($rawTotalSpRequired) ? (int) $rawTotalSpRequired : 0;

            $rawConfidence = $response['confidence'] ?? 0.85;
            $confidence = is_numeric($rawConfidence) ? (float) $rawConfidence : 0.85;

            $rawReasoning = $response['reasoning'] ?? 'Skill build recommended';
            $reasoning = is_string($rawReasoning) ? $rawReasoning : 'Skill build recommended';

            return [
                'build' => $build,
                'core_skills' => $coreSkills,
                'optional_skills' => $optionalSkills,
                'total_sp_required' => $totalSpRequired,
                'confidence' => $confidence,
                'reasoning' => $reasoning,
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill build recommendation failed', [
PATTERN;

$content = str_replace($old5, $new5, $content);

// === Fix 6: Fix analyzeSkillSynergies ===
$old6 = <<<'PATTERN'
            $response = $this->processWithMCPAgent($context);

            return [
                'synergies' => $response['synergies'] ?? [],
                'recommendations' => $response['recommendations'] ?? [],
                'confidence' => $response['confidence'] ?? 0.85,
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill synergy analysis failed', [
PATTERN;

$new6 = <<<'PATTERN'
            $response = $this->processWithMCPAgent($context);

            $rawSynergies = $response['synergies'] ?? null;
            /** @var array<int, array<string, mixed>> $synergies */
            $synergies = is_array($rawSynergies) ? array_values($rawSynergies) : [];

            $rawRecommendations = $response['recommendations'] ?? null;
            /** @var array<int, string> $recommendations */
            $recommendations = is_array($rawRecommendations) ? array_values($rawRecommendations) : [];

            $rawConfidence = $response['confidence'] ?? 0.85;
            $confidence = is_numeric($rawConfidence) ? (float) $rawConfidence : 0.85;

            return [
                'synergies' => $synergies,
                'recommendations' => $recommendations,
                'confidence' => $confidence,
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill synergy analysis failed', [
PATTERN;

$content = str_replace($old6, $new6, $content);

// === Fix 7: Fix getDefaultSPAllocation return type doc ===
$old7 = <<<'PATTERN'
    /**
     * Get default SP allocation
     *
     * @param  array<string, mixed>  $availableSkills
     * @return array{
     *     allocation: array<string, mixed>,
     *     priority_skills: array<string, mixed>,
     *     reasoning: string,
     *     confidence: float,
     *     metadata: array<string, mixed>
     * }
     */
PATTERN;

$new7 = <<<'PATTERN'
    /**
     * Get default SP allocation
     *
     * @param  array<string, mixed>  $availableSkills
     * @return array{
     *     allocation: array<string, mixed>,
     *     priority_skills: array<int, array<string, mixed>>,
     *     reasoning: string,
     *     confidence: float,
     *     metadata: array<string, mixed>
     * }
     */
PATTERN;

$content = str_replace($old7, $new7, $content);

// === Remove unused $startTime variables ===
$content = str_replace(
    "\$startTime = microtime(true);\n\n        try {",
    'try {',
    $content
);

file_put_contents($file, $content);
echo "All fixes applied successfully!\n";
