<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\Race;
use App\Models\Skill;
use App\Models\SupportCardDefinition;
use App\Models\TrainingSession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

describe('Complete User Workflows', function (): void {
    describe('Character Creation and Setup Workflow', function (): void {
        it('completes full character creation workflow', function (): void {
            // Step 1: Create character
            $characterResponse = $this->postJson('/api/v1/characters', [
                'name' => 'Test Uma',
                'scenario_type' => 'ura_finale',
                'speed_stat' => 100,
                'stamina_stat' => 100,
                'power_stat' => 100,
                'guts_stat' => 100,
                'wit_stat' => 100,
            ]);

            $characterResponse->assertCreated();
            $characterId = $characterResponse->json('data.id');

            // Step 2: Set up support deck (5 owned cards + 1 friend card = 6 total)
            $supportCards = SupportCardDefinition::factory()->count(6)->create(['is_active' => true]);

            foreach ($supportCards as $index => $card) {
                $isFriendCard = $index === 5; // Last card is friend card
                $deckResponse = $this->postJson("/api/v1/characters/{$characterId}/deck", [
                    'support_card_id' => $card->id,
                    'position_slot' => $index + 1,
                    'is_friend_card' => $isFriendCard,
                ]);

                $deckResponse->assertCreated();
            }

            // Step 3: Verify deck is complete
            $deckCheckResponse = $this->getJson("/api/v1/characters/{$characterId}/deck");
            $deckCheckResponse->assertSuccessful()
                ->assertJsonCount(6, 'data');

            // Step 4: Start career
            $careerResponse = $this->postJson('/api/v1/careers', [
                'character_id' => $characterId,
                'scenario_type' => 'ura_finale',
            ]);

            $careerResponse->assertCreated();
            $careerId = $careerResponse->json('data.id');

            // Verify career is active
            $careerCheckResponse = $this->getJson("/api/v1/careers/{$careerId}");
            $careerCheckResponse->assertSuccessful()
                ->assertJsonPath('data.status', 'active');
        });
    });

    describe('Training Session Workflow', function (): void {
        it('completes training session and tracks progress', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'status' => 'active',
                'current_turn' => 1,
            ]);

            // Step 1: Get training predictions
            $predictionResponse = $this->getJson("/api/v1/careers/{$career->id}/training-predictions");
            $predictionResponse->assertSuccessful();

            // Step 2: Execute training
            $trainingResponse = $this->postJson("/api/v1/careers/{$career->id}/training-sessions", [
                'training_type' => 'speed',
                'turn_number' => 1,
            ]);

            $trainingResponse->assertCreated();

            // Step 3: Verify stats updated
            $characterResponse = $this->getJson("/api/v1/characters/{$character->id}");
            $characterResponse->assertSuccessful();

            // Step 4: Check career progress
            $careerResponse = $this->getJson("/api/v1/careers/{$career->id}");
            $careerResponse->assertSuccessful();
        });
    });

    describe('Race Entry and Result Workflow', function (): void {
        it('completes race entry and records result', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'status' => 'active',
                'current_turn' => 25,
            ]);

            // Step 1: Get available races
            $racesResponse = $this->getJson("/api/v1/careers/{$career->id}/available-races");
            $racesResponse->assertSuccessful();

            // Step 2: Enter race
            $raceEntryResponse = $this->postJson("/api/v1/careers/{$career->id}/races", [
                'race_name' => 'Test G1 Race',
                'race_grade' => 'G1',
                'turn_number' => 25,
            ]);

            $raceEntryResponse->assertCreated();
            $raceId = $raceEntryResponse->json('data.id');

            // Step 3: Record race result
            $resultResponse = $this->putJson("/api/v1/careers/{$career->id}/races/{$raceId}", [
                'finish_position' => 1,
                'won_race' => true,
                'sp_reward' => 50,
            ]);

            $resultResponse->assertSuccessful();

            // Step 4: Verify race recorded
            $raceCheckResponse = $this->getJson("/api/v1/careers/{$career->id}/races");
            $raceCheckResponse->assertSuccessful()
                ->assertJsonCount(1, 'data');
        });
    });

    describe('Skill Acquisition Workflow', function (): void {
        it('completes skill hint to acquisition workflow', function (): void {
            $character = Character::factory()->create([
                'user_id' => $this->user->id,
                'available_sp' => 500,
            ]);

            $skill = Skill::factory()->create([
                'base_sp_cost' => 100,
                'is_active' => true,
            ]);

            // Step 1: Check available skills
            $skillsResponse = $this->getJson('/api/v1/skills');
            $skillsResponse->assertSuccessful();

            // Step 2: Get skill recommendations
            $recommendationsResponse = $this->getJson("/api/v1/skills/analysis/recommendations?character_id={$character->id}");
            $recommendationsResponse->assertSuccessful();

            // Step 3: Acquire skill
            $acquireResponse = $this->postJson("/api/v1/characters/{$character->id}/skills", [
                'skill_id' => $skill->id,
            ]);

            $acquireResponse->assertCreated();

            // Step 4: Verify skill acquired
            $characterSkillsResponse = $this->getJson("/api/v1/characters/{$character->id}/skills");
            $characterSkillsResponse->assertSuccessful()
                ->assertJsonCount(1, 'data');

            // Step 5: Verify SP deducted
            $character->refresh();
            expect($character->available_sp)->toBe(400);
        });
    });

    describe('Career Completion Workflow', function (): void {
        it('completes full career and generates report', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'status' => 'active',
                'current_turn' => 72,
            ]);

            // Add training sessions
            TrainingSession::factory()->count(50)->create([
                'career_id' => $career->id,
                'speed_gain' => 10,
            ]);

            // Add races
            Race::factory()->count(10)->create([
                'career_id' => $career->id,
                'won_race' => true,
            ]);

            // Step 1: Complete career
            $completeResponse = $this->putJson("/api/v1/careers/{$career->id}", [
                'status' => 'completed',
            ]);

            $completeResponse->assertSuccessful();

            // Step 2: Generate career report
            $reportResponse = $this->getJson("/api/v1/careers/{$career->id}/report");
            $reportResponse->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'career_summary',
                        'stat_progression',
                        'training_analysis',
                        'race_performance',
                    ],
                ]);

            // Step 3: Get career statistics
            $statsResponse = $this->getJson("/api/v1/careers/{$career->id}/statistics");
            $statsResponse->assertSuccessful();
        });
    });

    describe('Deck Optimization Workflow', function (): void {
        it('analyzes and optimizes support deck', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);

            // Set up initial deck
            $cards = SupportCardDefinition::factory()->count(6)->create([
                'card_type' => 'speed',
                'meta_tier' => 'B',
                'is_active' => true,
            ]);

            foreach ($cards as $index => $card) {
                CharacterSupportCard::factory()->create([
                    'character_id' => $character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => $index + 1,
                ]);
            }

            // Step 1: Analyze current deck
            $analysisResponse = $this->getJson("/api/v1/characters/{$character->id}/deck/analysis");
            $analysisResponse->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'composition',
                        'synergy',
                        'recommendations',
                    ],
                ]);

            // Step 2: Get optimization suggestions
            $optimizationResponse = $this->getJson("/api/v1/characters/{$character->id}/deck/optimization");
            $optimizationResponse->assertSuccessful();

            // Step 3: Get recommended replacements
            $recommendationsResponse = $this->getJson("/api/v1/characters/{$character->id}/deck/recommendations");
            $recommendationsResponse->assertSuccessful();
        });
    });

    describe('Multi-Career Comparison Workflow', function (): void {
        it('compares multiple careers and identifies patterns', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);

            // Create multiple completed careers
            $careers = Career::factory()->count(3)->create([
                'character_id' => $character->id,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            foreach ($careers as $career) {
                TrainingSession::factory()->count(30)->create([
                    'career_id' => $career->id,
                ]);
                Race::factory()->count(5)->create([
                    'career_id' => $career->id,
                ]);
            }

            $careerIds = $careers->pluck('id')->toArray();

            // Step 1: Compare careers
            $comparisonResponse = $this->postJson('/api/v1/careers/compare', [
                'career_ids' => $careerIds,
            ]);

            $comparisonResponse->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'careers',
                        'comparison_summary',
                        'stat_comparison',
                        'best_performer',
                    ],
                ]);

            // Step 2: Identify success patterns
            $patternsResponse = $this->postJson('/api/v1/careers/patterns', [
                'career_ids' => $careerIds,
            ]);

            $patternsResponse->assertSuccessful();

            // Step 3: Get recommendations based on patterns
            $recommendationsResponse = $this->postJson('/api/v1/careers/recommendations', [
                'career_ids' => $careerIds,
            ]);

            $recommendationsResponse->assertSuccessful();
        });
    });
});
