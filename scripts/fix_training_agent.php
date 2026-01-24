<?php

/**
 * Fix PHPStan errors in TrainingOptimizationAgent.php
 */
$file = __DIR__.'/app/Services/AI/Agents/TrainingOptimizationAgent.php';
$content = file_get_contents($file);
$original = $content;

// Fix 1: analyzeTrainingOptions - Line 58 returns array<string, mixed>
// Need to change getDefaultRecommendations return type annotation to match expected type
$content = str_replace(
    <<<'OLD'
    /**
     * Get default recommendations when MCP is unavailable
     *
     * @param  array<string, mixed>  $trainingOptions
     * @return array<string, mixed>
     */
    protected function getDefaultRecommendations(array $trainingOptions): array
    {
        return [
            'recommendations' => array_map(function ($option, $index) {
                return [
                    'option_index' => $index,
                    'priority' => 1.0 / ($index + 1),
                    'reasoning' => 'Default recommendation',
                ];
            }, $trainingOptions, array_keys($trainingOptions)),
OLD,
    <<<'NEW'
    /**
     * Get default recommendations when MCP is unavailable
     *
     * @param  array<string, mixed>  $trainingOptions
     * @return array{
     *     recommendations: array<int, array<string, mixed>>,
     *     analysis: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string,
     *     metadata: array<string, mixed>
     * }
     */
    protected function getDefaultRecommendations(array $trainingOptions): array
    {
        /** @var array<int, mixed> $keys */
        $keys = array_keys($trainingOptions);
        
        /** @var array<int, array<string, mixed>> $recommendations */
        $recommendations = [];
        foreach ($trainingOptions as $index => $option) {
            $idx = is_int($index) ? $index : 0;
            $recommendations[] = [
                'option_index' => $idx,
                'priority' => 1.0 / ($idx + 1),
                'reasoning' => 'Default recommendation',
            ];
        }
        
        return [
            'recommendations' => $recommendations,
NEW,
    $content
);

// Fix 2: predictStatGains - Line 136 - need to type cast mixed values properly
$content = str_replace(
    <<<'OLD'
            $response = $this->processWithMCPAgent($context);

            return [
                'stat_gains' => $response['stat_gains'] ?? [],
                'energy_cost' => $response['energy_cost'] ?? 0,
                'failure_risk' => $response['failure_risk'] ?? 0.0,
                'spirit_burst_potential' => $response['spirit_burst_potential'] ?? null,
                'skill_hints' => $response['skill_hints'] ?? [],
                'confidence' => $response['confidence'] ?? 0.8,
            ];
OLD,
    <<<'NEW'
            $response = $this->processWithMCPAgent($context);

            // Extract and type-validate response values
            $rawStatGains = $response['stat_gains'] ?? [];
            /** @var array<string, int> $statGains */
            $statGains = is_array($rawStatGains) 
                ? array_map(fn ($v): int => is_numeric($v) ? (int) $v : 0, $rawStatGains)
                : [];
            
            $rawEnergyCost = $response['energy_cost'] ?? 0;
            $energyCost = is_numeric($rawEnergyCost) ? (int) $rawEnergyCost : 0;
            
            $rawFailureRisk = $response['failure_risk'] ?? 0.0;
            $failureRisk = is_numeric($rawFailureRisk) ? (float) $rawFailureRisk : 0.0;
            
            $rawSpiritBurst = $response['spirit_burst_potential'] ?? null;
            $spiritBurstPotential = $rawSpiritBurst !== null && is_numeric($rawSpiritBurst) 
                ? (float) $rawSpiritBurst 
                : null;
            
            $rawSkillHints = $response['skill_hints'] ?? [];
            /** @var array<int, string> $skillHints */
            $skillHints = is_array($rawSkillHints) 
                ? array_values(array_filter(array_map(fn ($v): string => is_string($v) ? $v : '', $rawSkillHints)))
                : [];
            
            $rawConfidence = $response['confidence'] ?? 0.8;
            $confidence = is_numeric($rawConfidence) ? (float) $rawConfidence : 0.8;

            return [
                'stat_gains' => $statGains,
                'energy_cost' => $energyCost,
                'failure_risk' => $failureRisk,
                'spirit_burst_potential' => $spiritBurstPotential,
                'skill_hints' => $skillHints,
                'confidence' => $confidence,
            ];
NEW,
    $content
);

// Fix 3: predictStatGains error return - Line 150 - need proper return type for getDefaultStatGainPrediction
$content = str_replace(
    <<<'OLD'
    /**
     * Get default stat gain prediction
     *
     * @param  array<string, mixed>  $trainingOption
     * @return array<string, mixed>
     */
    protected function getDefaultStatGainPrediction(array $trainingOption): array
OLD,
    <<<'NEW'
    /**
     * Get default stat gain prediction
     *
     * @param  array<string, mixed>  $trainingOption
     * @return array{
     *     stat_gains: array<string, int>,
     *     energy_cost: int,
     *     failure_risk: float,
     *     spirit_burst_potential: float|null,
     *     skill_hints: array<int, string>,
     *     confidence: float
     * }
     */
    protected function getDefaultStatGainPrediction(array $trainingOption): array
OLD,
    $content
);

// Fix 4: optimizeTrainingSequence - Line 182 - need to type cast mixed values properly
$content = str_replace(
    <<<'OLD'
            $response = $this->processWithMCPAgent($context);

            return [
                'sequence' => $response['sequence'] ?? [],
                'expected_outcomes' => $response['expected_outcomes'] ?? [],
                'confidence' => $response['confidence'] ?? 0.8,
                'reasoning' => $response['reasoning'] ?? 'Optimized for goal achievement',
            ];
OLD,
    <<<'NEW'
            $response = $this->processWithMCPAgent($context);

            // Extract and type-validate response values
            $rawSequence = $response['sequence'] ?? [];
            /** @var array<int, array<string, mixed>> $sequence */
            $sequence = is_array($rawSequence) ? array_values($rawSequence) : [];
            
            $rawOutcomes = $response['expected_outcomes'] ?? [];
            /** @var array<string, mixed> $expectedOutcomes */
            $expectedOutcomes = is_array($rawOutcomes) ? $rawOutcomes : [];
            
            $rawConfidence = $response['confidence'] ?? 0.8;
            $confidence = is_numeric($rawConfidence) ? (float) $rawConfidence : 0.8;
            
            $rawReasoning = $response['reasoning'] ?? 'Optimized for goal achievement';
            $reasoning = is_string($rawReasoning) ? $rawReasoning : 'Optimized for goal achievement';

            return [
                'sequence' => $sequence,
                'expected_outcomes' => $expectedOutcomes,
                'confidence' => $confidence,
                'reasoning' => $reasoning,
            ];
NEW,
    $content
);

// Fix 5: parseRecommendations return type
$content = str_replace(
    <<<'OLD'
    /**
     * Parse recommendations from MCP response
     *
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $trainingOptions
     * @return array<string, mixed>
     */
    protected function parseRecommendations(array $response, array $trainingOptions): array
    {
        return [
            'recommendations' => $response['recommendations'] ?? [],
            'analysis' => $response['analysis'] ?? [],
            'confidence' => $response['confidence'] ?? 0.8,
            'reasoning' => $response['reasoning'] ?? 'Analysis completed',
        ];
    }
OLD,
    <<<'NEW'
    /**
     * Parse recommendations from MCP response
     *
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $trainingOptions
     * @return array{
     *     recommendations: array<int, array<string, mixed>>,
     *     analysis: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string
     * }
     */
    protected function parseRecommendations(array $response, array $trainingOptions): array
    {
        $rawRecommendations = $response['recommendations'] ?? [];
        /** @var array<int, array<string, mixed>> $recommendations */
        $recommendations = is_array($rawRecommendations) ? array_values($rawRecommendations) : [];
        
        $rawAnalysis = $response['analysis'] ?? [];
        /** @var array<string, mixed> $analysis */
        $analysis = is_array($rawAnalysis) ? $rawAnalysis : [];
        
        $rawConfidence = $response['confidence'] ?? 0.8;
        $confidence = is_numeric($rawConfidence) ? (float) $rawConfidence : 0.8;
        
        $rawReasoning = $response['reasoning'] ?? 'Analysis completed';
        $reasoning = is_string($rawReasoning) ? $rawReasoning : 'Analysis completed';

        return [
            'recommendations' => $recommendations,
            'analysis' => $analysis,
            'confidence' => $confidence,
            'reasoning' => $reasoning,
        ];
    }
NEW,
    $content
);

if ($content !== $original) {
    file_put_contents($file, $content);
    echo "Fixed TrainingOptimizationAgent.php\n";

    // Check syntax
    exec('php -l '.escapeshellarg($file).' 2>&1', $output, $returnCode);
    echo implode("\n", $output)."\n";

    if ($returnCode === 0) {
        echo "Syntax OK\n";
    } else {
        echo "SYNTAX ERROR - reverting\n";
        file_put_contents($file, $original);
    }
} else {
    echo "No changes made - patterns not found\n";
}
