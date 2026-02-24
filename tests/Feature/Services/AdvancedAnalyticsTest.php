<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\Analytics\CareerComparisonAnalyticsService;
use App\Services\Analytics\PatternRecognitionService;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

describe('PatternRecognitionService', function () {
    it('extracts stat vectors from careers', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $careers = collect([
            Career::factory()->create([
                'character_id' => $character->id,
                'final_speed' => 800,
                'final_stamina' => 700,
                'final_power' => 600,
                'final_guts' => 500,
                'final_wit' => 400,
            ]),
        ]);

        $service = new PatternRecognitionService;
        $vectors = $service->extractStatVectors($careers);

        expect($vectors)->toHaveCount(1)
            ->and($vectors[0]['vector']['speed'])->toBe(800.0)
            ->and($vectors[0]['vector']['stamina'])->toBe(700.0)
            ->and($vectors[0]['vector']['power'])->toBe(600.0)
            ->and($vectors[0]['vector']['guts'])->toBe(500.0)
            ->and($vectors[0]['vector']['wit'])->toBe(400.0);
    });

    it('calculates euclidean distance correctly', function () {
        $service = new PatternRecognitionService;

        $distance = $service->euclideanDistance(
            ['speed' => 0, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 0],
            ['speed' => 3, 'stamina' => 4, 'power' => 0, 'guts' => 0, 'wit' => 0]
        );

        expect($distance)->toBe(5.0);
    });

    it('returns empty clusters when fewer data points than k', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $careers = collect([
            Career::factory()->create(['character_id' => $character->id]),
        ]);

        $service = new PatternRecognitionService;
        $result = $service->clusterCareers($careers, 3);

        expect($result['clusters'])->toBeEmpty()
            ->and($result['converged'])->toBeFalse();
    });

    it('clusters careers into k groups', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $careers = collect();
        for ($i = 0; $i < 6; $i++) {
            $careers->push(Career::factory()->create([
                'character_id' => $character->id,
                'final_speed' => rand(100, 1200),
                'final_stamina' => rand(100, 1200),
                'final_power' => rand(100, 1200),
                'final_guts' => rand(100, 1200),
                'final_wit' => rand(100, 1200),
            ]));
        }

        $service = new PatternRecognitionService;
        $result = $service->clusterCareers($careers, 3);

        expect($result['clusters'])->toHaveCount(3)
            ->and($result['iterations'])->toBeGreaterThan(0);

        $totalMembers = collect($result['clusters'])->sum('size');
        expect($totalMembers)->toBe(6);
    });

    it('mines association rules from career data', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $careers = collect();
        for ($i = 0; $i < 10; $i++) {
            $careers->push(Career::factory()->create([
                'character_id' => $character->id,
                'scenario_type' => 'ura_finale',
                'status' => 'completed',
                'final_speed' => 900,
                'final_stamina' => 900,
                'final_power' => 900,
                'final_guts' => 900,
                'final_wit' => 900,
            ]));
        }

        $service = new PatternRecognitionService;
        $result = $service->mineAssociationRules($careers);

        expect($result)->toHaveKeys(['rules', 'frequent_itemsets'])
            ->and($result['frequent_itemsets'])->not->toBeEmpty();
    });

    it('returns empty rules for empty career collection', function () {
        $service = new PatternRecognitionService;
        $result = $service->mineAssociationRules(collect());

        expect($result['rules'])->toBeEmpty()
            ->and($result['frequent_itemsets'])->toBeEmpty();
    });

    it('identifies training patterns for a character', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['character_id' => $character->id]);

        TrainingSession::factory()->count(5)->create([
            'career_id' => $career->id,
            'character_id' => $character->id,
            'training_type' => 'speed',
            'career_phase' => 'junior',
        ]);

        TrainingSession::factory()->count(3)->create([
            'career_id' => $career->id,
            'character_id' => $character->id,
            'training_type' => 'stamina',
            'career_phase' => 'classic',
        ]);

        $service = new PatternRecognitionService;
        $result = $service->identifyTrainingPatterns($character);

        expect($result)->toHaveKeys(['dominant_training_types', 'phase_patterns', 'efficiency_by_type'])
            ->and($result['dominant_training_types'])->toHaveKey('speed')
            ->and($result['dominant_training_types']['speed'])->toBe(5);
    });
});

describe('CareerComparisonAnalyticsService', function () {
    it('generates parallel coordinates data', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $career1 = Career::factory()->create([
            'character_id' => $character->id,
            'career_name' => 'Run A',
            'final_speed' => 800,
            'final_stamina' => 700,
            'final_power' => 600,
            'final_guts' => 500,
            'final_wit' => 400,
            'final_sp' => 300,
            'current_turn' => 72,
        ]);

        $career2 = Career::factory()->create([
            'character_id' => $character->id,
            'career_name' => 'Run B',
            'final_speed' => 400,
            'final_stamina' => 500,
            'final_power' => 600,
            'final_guts' => 700,
            'final_wit' => 800,
            'final_sp' => 400,
            'current_turn' => 72,
        ]);

        $service = new CareerComparisonAnalyticsService;
        $result = $service->generateParallelCoordinatesData([$career1->id, $career2->id]);

        expect($result)->toHaveKeys(['axes', 'series'])
            ->and($result['series'])->toHaveCount(2)
            ->and($result['series'][0]['career_name'])->toBe('Run A')
            ->and($result['series'][0]['values']['speed'])->toBe(800.0);
    });

    it('identifies divergence points between careers', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $career1 = Career::factory()->create(['character_id' => $character->id]);
        $career2 = Career::factory()->create(['character_id' => $character->id]);

        for ($turn = 1; $turn <= 5; $turn++) {
            TrainingSession::factory()->create([
                'career_id' => $career1->id,
                'character_id' => $character->id,
                'turn_number' => $turn,
                'speed_gain' => 20,
                'stamina_gain' => 5,
                'power_gain' => 5,
                'guts_gain' => 5,
                'wit_gain' => 5,
            ]);

            TrainingSession::factory()->create([
                'career_id' => $career2->id,
                'character_id' => $character->id,
                'turn_number' => $turn,
                'speed_gain' => 5,
                'stamina_gain' => 20,
                'power_gain' => 5,
                'guts_gain' => 5,
                'wit_gain' => 5,
            ]);
        }

        $service = new CareerComparisonAnalyticsService;
        $result = $service->identifyDivergencePoints([$career1->id, $career2->id]);

        expect($result)->toHaveKeys(['divergence_points', 'summary'])
            ->and($result['summary'])->toHaveKeys(['earliest_divergence', 'most_divergent_stat', 'max_magnitude']);
    });

    it('returns empty divergence for single career', function () {
        $service = new CareerComparisonAnalyticsService;
        $result = $service->identifyDivergencePoints([1]);

        expect($result['divergence_points'])->toBeEmpty()
            ->and($result['summary']['earliest_divergence'])->toBeNull();
    });

    it('calculates comparison summary with rankings', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $career1 = Career::factory()->create([
            'character_id' => $character->id,
            'final_speed' => 1000,
            'final_stamina' => 1000,
            'final_power' => 1000,
            'final_guts' => 1000,
            'final_wit' => 1000,
        ]);

        $career2 = Career::factory()->create([
            'character_id' => $character->id,
            'final_speed' => 500,
            'final_stamina' => 500,
            'final_power' => 500,
            'final_guts' => 500,
            'final_wit' => 500,
        ]);

        $service = new CareerComparisonAnalyticsService;
        $result = $service->calculateComparisonSummary([$career1->id, $career2->id]);

        expect($result)->toHaveKeys(['careers', 'averages', 'std_deviations'])
            ->and($result['careers'])->toHaveCount(2)
            ->and($result['careers'][0]['rank'])->toBe(1)
            ->and($result['careers'][0]['total_stats'])->toBe(5000)
            ->and($result['careers'][1]['rank'])->toBe(2)
            ->and($result['careers'][1]['total_stats'])->toBe(2500)
            ->and($result['averages']['speed'])->toBe(750.0);
    });

    it('generates progression chart data', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['character_id' => $character->id]);

        TrainingSession::factory()->create([
            'career_id' => $career->id,
            'character_id' => $character->id,
            'turn_number' => 1,
            'speed_gain' => 10,
            'stamina_gain' => 5,
            'power_gain' => 3,
            'guts_gain' => 2,
            'wit_gain' => 1,
        ]);

        $service = new CareerComparisonAnalyticsService;
        $result = $service->generateProgressionChartData([$career->id]);

        expect($result)->toHaveKeys(['labels', 'datasets'])
            ->and($result['labels'])->toContain(1)
            ->and($result['datasets'])->not->toBeEmpty();
    });
});

describe('PatternDashboard Livewire Component', function () {
    it('renders the pattern dashboard component', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Analytics\PatternDashboard::class)
            ->assertSuccessful()
            ->assertSee('Select Careers to Analyze');
    });

    it('validates minimum career selection', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Analytics\PatternDashboard::class)
            ->set('selectedCareerIds', [])
            ->call('analyzePatterns')
            ->assertHasErrors('selectedCareerIds');
    });

    it('switches tabs correctly', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Analytics\PatternDashboard::class)
            ->assertSet('activeTab', 'patterns')
            ->call('setActiveTab', 'comparison')
            ->assertSet('activeTab', 'comparison')
            ->call('setActiveTab', 'divergence')
            ->assertSet('activeTab', 'divergence');
    });

    it('requires authentication', function () {
        $this->get('/analytics/patterns')
            ->assertRedirect('/');
    });
});
