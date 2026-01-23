<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\CareerAnalyticsService;
use App\Services\CareerReportingService;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $analyticsService = app(CareerAnalyticsService::class);
    $this->service = new CareerReportingService($analyticsService);
    Cache::flush();
});

describe('CareerReportingService', function (): void {
    describe('generateCareerSummaryReport', function (): void {
        it('generates comprehensive career summary report', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career = Career::factory()->create([
                'character_id' => $character->id,
                'scenario_type' => 'ura_finale',
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            TrainingSession::factory()->count(20)->create([
                'career_id' => $career->id,
                'training_type' => 'speed',
                'speed_gain' => 10,
            ]);

            Race::factory()->count(5)->create([
                'career_id' => $career->id,
                'won_race' => true,
            ]);

            $result = $this->service->generateCareerSummaryReport($career);

            expect($result)->toHaveKey('report_metadata')
                ->and($result)->toHaveKey('executive_summary')
                ->and($result)->toHaveKey('performance_overview')
                ->and($result)->toHaveKey('training_analysis')
                ->and($result)->toHaveKey('race_analysis')
                ->and($result)->toHaveKey('skill_analysis')
                ->and($result)->toHaveKey('key_insights')
                ->and($result)->toHaveKey('recommendations')
                ->and($result)->toHaveKey('statistical_summary');
        });

        it('includes correct report metadata', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id, 'name' => 'Test Character']);

            $career = Career::factory()->create([
                'character_id' => $character->id,
                'scenario_type' => 'ura_finale',
            ]);

            $result = $this->service->generateCareerSummaryReport($career);

            expect($result['report_metadata'])->toHaveKey('report_id')
                ->and($result['report_metadata'])->toHaveKey('generated_at')
                ->and($result['report_metadata']['career_id'])->toBe($career->id)
                ->and($result['report_metadata']['character_name'])->toBe('Test Character')
                ->and($result['report_metadata']['scenario_type'])->toBe('ura_finale');
        });
    });

    describe('exportToJson', function (): void {
        it('exports report in JSON format', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career = Career::factory()->create(['character_id' => $character->id]);

            $result = $this->service->exportToJson($career);

            expect($result)->toBeString();

            $decoded = json_decode($result, true);
            expect($decoded)->toBeArray()
                ->and($decoded)->toHaveKey('report_metadata')
                ->and($decoded)->toHaveKey('executive_summary');
        });
    });

    describe('exportToCsv', function (): void {
        it('exports report in CSV format', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career = Career::factory()->create(['character_id' => $character->id]);

            TrainingSession::factory()->count(5)->create(['career_id' => $career->id]);

            $result = $this->service->exportToCsv($career);

            expect($result)->toHaveKey('headers')
                ->and($result)->toHaveKey('rows')
                ->and($result['headers'])->toBeArray()
                ->and($result['rows'])->toBeArray();
        });
    });

    describe('exportToPdfFormat', function (): void {
        it('returns PDF-ready format data', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career = Career::factory()->create([
                'character_id' => $character->id,
                'status' => 'completed',
            ]);

            TrainingSession::factory()->count(10)->create([
                'career_id' => $career->id,
                'speed_gain' => 10,
                'stamina_gain' => 8,
            ]);

            $result = $this->service->exportToPdfFormat($career);

            expect($result)->toHaveKey('title')
                ->and($result)->toHaveKey('sections')
                ->and($result)->toHaveKey('generated_at')
                ->and($result['sections'])->toBeArray();
        });
    });

    describe('generateCharacterReport', function (): void {
        it('generates character report with career history', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            Career::factory()->count(3)->create([
                'character_id' => $character->id,
                'status' => 'completed',
            ]);

            $result = $this->service->generateCharacterReport($character);

            expect($result)->toHaveKey('character_info')
                ->and($result)->toHaveKey('career_history')
                ->and($result)->toHaveKey('aggregate_statistics')
                ->and($result)->toHaveKey('performance_trends')
                ->and($result)->toHaveKey('improvement_areas')
                ->and($result)->toHaveKey('strengths');
        });
    });

    describe('clearCareerReportCache', function (): void {
        it('clears cache for career report', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            // Generate report to populate cache
            $this->service->generateCareerSummaryReport($career);

            // Clear cache
            $this->service->clearCareerReportCache($career);

            // Verify cache is cleared by checking it doesn't exist
            expect(Cache::has("report:career_summary:{$career->id}"))->toBeFalse();
        });
    });
});
