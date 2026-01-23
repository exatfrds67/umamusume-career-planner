<?php

namespace App\Services;

use App\Models\Character;

class SynergyScorer
{
    /**
     * Calculate overall deck synergy score
     *
     * @return array{score: float, breakdown: array<string, mixed>, recommendations: array<int, string>}
     */
    public function calculateDeckScore(): array
        $deck = $character->supportCards()->with('supportCard')->get();

        if ($deck->isEmpty()) {
            return [
                'score' => 0,
                'breakdown' => [],
                'recommendations' => ['Add support cards to your deck'],
            ];
        }

        $scores = [
            'meta_quality' => $this->scoreMetaQuality($deck),
            'type_diversity' => $this->scoreTypeDiversity($deck),
            'stat_alignment' => $this->scoreStatAlignment($character, $deck),
            'limit_break_level' => $this->scoreLimitBreaks($deck),
            'friendship_bonus' => $this->scoreFriendship($deck),
        ];

        // Weighted average
        $weights = [
            'meta_quality' => 0.30,
            'type_diversity' => 0.25,
            'stat_alignment' => 0.25,
            'limit_break_level' => 0.10,
            'friendship_bonus' => 0.10,
        ];

        $totalScore = 0;
        foreach ($scores as $key => $score) {
            $totalScore = ($totalScore ?? 0) + $score * $weights[$key];
        }

        $recommendations = $this->generateRecommendations($scores, $deck);

        return [
            'score' => round($totalScore, 1),
            'breakdown' => $scores,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Score based on meta tier quality
     */
    private function scoreMetaQuality($deck): float
    {
        $tierScores = [
            'S+' => 100,
            'S' => 85,
            'A' => 70,
            'B' => 50,
            'C' => 30,
        ];

        $totalScore = 0;
        $count = 0;

        foreach ($deck as $characterCard) {
            if ($characterCard->supportCard) {
                $tier = $characterCard->supportCard->meta_tier;
                $totalScore = ($totalScore ?? 0) + $tierScores[$tier] ?? 0;
                $count = ($count ?? 0) + 1;
            }
        }

        return $count > 0 ? $totalScore / $count : 0;
    }

    /**
     * Score based on type diversity
     */
    private function scoreTypeDiversity($deck): float
    {
        $types = [];
        foreach ($deck as $characterCard) {
            if ($characterCard->supportCard) {
                $types[] = $characterCard->supportCard->card_type;
            }
        }

        $uniqueTypes = count(array_unique($types));
        $totalCards = count($types);

        if ($totalCards === 0) {
            return 0;
        }

        // Ideal is 5-6 different types
        $diversityRatio = $uniqueTypes / 6;

        return min(100, $diversityRatio * 100);
    }

    /**
     * Score based on alignment with character stats
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     */
    private function scoreStatAlignment(Character $character, $deck): float
    {
        // Get character's current stat priorities
        /** @var array<string, int> $stats */
        $stats = $character->current_stats ?? [];
        if (empty($stats)) {
            return 75; // Neutral score if no stats defined
        }

        // Determine which stats need the most improvement
        $statValues = [
            'speed' => $stats['speed'] ?? 0,
            'stamina' => $stats['stamina'] ?? 0,
            'power' => $stats['power'] ?? 0,
            'guts' => $stats['guts'] ?? 0,
            'wit' => $stats['wit'] ?? 0,
        ];

        arsort($statValues);
        /** @var array<int, string> $weakestStats */
        $weakestStats = array_slice(array_keys($statValues), -3, 3, true);

        // Check if deck covers weak stats
        $coverage = 0;
        foreach ($deck as $characterCard) {
            if ($characterCard->supportCard) {
                $cardType = $characterCard->supportCard->card_type;
                if (in_array($cardType, $weakestStats)) {
                    $coverage = ($coverage ?? 0) + 1;
                }
            }
        }

        return min(100, ($coverage / 3) * 100);
    }

    /**
     * Score based on limit break levels
     */
    private function scoreLimitBreaks($deck): float
    {
        $totalLB = 0;
        $maxPossible = $deck->count() * 4; // Max 4 LB per card

        foreach ($deck as $characterCard) {
            $totalLB = ($totalLB ?? 0) + $characterCard->limit_break_level ?? 0;
        }

        return $maxPossible > 0 ? ($totalLB / $maxPossible) * 100 : 0;
    }

    /**
     * Score based on friendship levels
     */
    private function scoreFriendship($deck): float
    {
        $totalFriendship = 0;
        $maxPossible = $deck->count() * 100; // Max 100 per card

        foreach ($deck as $characterCard) {
            $totalFriendship = ($totalFriendship ?? 0) + $characterCard->friendship_level ?? 0;
        }

        return $maxPossible > 0 ? ($totalFriendship / $maxPossible) * 100 : 0;
    }

    /**
     * Generate recommendations based on scores
     *
     * @param  array<string, float>  $scores
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     * @return array<int, string>
     */
    private function generateRecommendations(): array
        /** @var array<int, string> $recommendations */
        $recommendations = [];

        if ($scores['meta_quality'] < 70) {
            $recommendations[] = 'Consider replacing lower-tier cards with S or S+ tier cards';
        }

        if ($scores['type_diversity'] < 60) {
            $recommendations[] = 'Add more diverse card types for better training coverage';
        }

        if ($scores['stat_alignment'] < 60) {
            $recommendations[] = 'Focus on cards that boost your weakest stats';
        }

        if ($scores['limit_break_level'] < 50) {
            $recommendations[] = 'Invest in limit breaking your support cards for better bonuses';
        }

        if ($scores['friendship_bonus'] < 50) {
            $recommendations[] = 'Train with your support cards to increase friendship levels';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Your deck is well-optimized! Keep training to maximize bond levels';
        }

        return $recommendations;
    }
}
