<?php

declare(strict_types=1);

use App\Services\OCR\DataExtractionService;
use App\Services\OCR\DataValidationService;
use App\Services\OCR\Parsers\CharacterStatsParser;
use App\Services\OCR\Parsers\RaceResultParser;
use App\Services\OCR\Parsers\SkillListParser;
use App\Services\OCR\Parsers\TrainingSessionParser;

beforeEach(function () {
    $this->statsParser = new CharacterStatsParser;
    $this->trainingParser = new TrainingSessionParser;
    $this->raceParser = new RaceResultParser;
    $this->skillParser = new SkillListParser;
    $this->validator = new DataValidationService;

    $this->service = new DataExtractionService(
        $this->statsParser,
        $this->trainingParser,
        $this->raceParser,
        $this->skillParser,
        $this->validator
    );
});

describe('Character Stats Extraction', function () {
    it('extracts character stats successfully', function () {
        $ocrText = <<<'TEXT'
        ウマ娘: サイレンススズカ
        ターン: 24/78
        体力: 75
        やる気: 好調
        スピード: 850
        スタミナ: 720
        パワー: 680
        根性: 450
        賢さ: 590
        TEXT;

        $result = $this->service->extractCharacterStats($ocrText);

        expect($result['success'])->toBeTrue()
            ->and($result['screen_type'])->toBe('character_stats')
            ->and($result['confidence'])->toBeGreaterThan(0.8)
            ->and($result['data']['stats']['speed'])->toBe(850)
            ->and($result['data']['stats']['stamina'])->toBe(720)
            ->and($result['data']['stats']['power'])->toBe(680)
            ->and($result['data']['stats']['guts'])->toBe(450)
            ->and($result['data']['stats']['wit'])->toBe(590)
            ->and($result['data']['energy_level'])->toBe(75)
            ->and($result['data']['mood_status'])->toBe('good')
            ->and($result['data']['current_turn'])->toBe(24)
            ->and($result['data']['total_turns'])->toBe(78);
    });

    it('filters out invalid stat values during extraction', function () {
        $ocrText = <<<'TEXT'
        スピード: 1500
        スタミナ: 800
        パワー: 600
        TEXT;

        $result = $this->service->extractCharacterStats($ocrText);

        // Invalid speed value (1500) is filtered out during extraction
        expect($result['data']['stats']['speed'])->toBeNull()
            ->and($result['data']['stats']['stamina'])->toBe(800)
            ->and($result['data']['stats']['power'])->toBe(600)
            ->and($result['validation']['warnings'])->toContain('Only 2 stats extracted (expected 5)');
    });

    it('handles partial stat extraction', function () {
        $ocrText = <<<'TEXT'
        スピード: 850
        スタミナ: 720
        TEXT;

        $result = $this->service->extractCharacterStats($ocrText);

        expect($result['data']['stats']['speed'])->toBe(850)
            ->and($result['data']['stats']['stamina'])->toBe(720)
            ->and($result['data']['stats']['power'])->toBeNull()
            ->and($result['validation']['warnings'])->toContain('Only 2 stats extracted (expected 5)');
    });
});

describe('Training Session Extraction', function () {
    it('extracts training session data successfully', function () {
        $ocrText = <<<'TEXT'
        トレーニング: スピード
        スピード +45
        パワー +20
        体力 -25
        スキルヒント
        友情トレーニング
        TEXT;

        $result = $this->service->extractTrainingSession($ocrText);

        expect($result['success'])->toBeTrue()
            ->and($result['data']['training_type'])->toBe('speed')
            ->and($result['data']['stat_gains']['speed'])->toBe(45)
            ->and($result['data']['stat_gains']['power'])->toBe(20)
            ->and($result['data']['energy_cost'])->toBe(25)
            ->and($result['data']['has_skill_hint'])->toBeTrue()
            ->and($result['data']['has_friendship'])->toBeTrue();
    });

    it('validates training type correctly', function () {
        $ocrText = <<<'TEXT'
        トレーニング: InvalidType
        スピード +45
        TEXT;

        $result = $this->service->extractTrainingSession($ocrText);

        expect($result['validation']['warnings'])->toContain('Training type not extracted');
    });

    it('detects Spirit Burst indicator', function () {
        $ocrText = <<<'TEXT'
        トレーニング: スピード
        スピード +45
        スピリットバースト
        TEXT;

        $result = $this->service->extractTrainingSession($ocrText);

        expect($result['data']['has_spirit_burst'])->toBeTrue();
    });
});

describe('Race Result Extraction', function () {
    it('extracts race result data successfully', function () {
        $ocrText = <<<'TEXT'
        レース: 日本ダービー
        G1
        2400m
        芝
        着順: 1位
        ファン +15000
        SP +120
        TEXT;

        $result = $this->service->extractRaceResult($ocrText);

        expect($result['success'])->toBeTrue()
            ->and($result['data']['race_name'])->toContain('日本ダービー')
            ->and($result['data']['race_grade'])->toBe('G1')
            ->and($result['data']['distance'])->toBe(2400)
            ->and($result['data']['distance_category'])->toBe('medium')
            ->and($result['data']['surface'])->toBe('turf')
            ->and($result['data']['position'])->toBe(1)
            ->and($result['data']['outcome'])->toBe('victory')
            ->and($result['data']['fans_gained'])->toBe(15000)
            ->and($result['data']['skill_points_gained'])->toBe(120);
    });

    it('categorizes distances correctly', function () {
        $testCases = [
            ['distance' => 1200, 'expected' => 'sprint'],
            ['distance' => 1600, 'expected' => 'mile'],
            ['distance' => 2000, 'expected' => 'medium'],
            ['distance' => 3000, 'expected' => 'long'],
        ];

        foreach ($testCases as $case) {
            $ocrText = "距離: {$case['distance']}m\n着順: 1位";
            $result = $this->service->extractRaceResult($ocrText);

            expect($result['data']['distance_category'])->toBe($case['expected']);
        }
    });

    it('validates race position range', function () {
        $ocrText = <<<'TEXT'
        レース: テストレース
        着順: 25位
        TEXT;

        $result = $this->service->extractRaceResult($ocrText);

        expect($result['validation']['valid'])->toBeFalse()
            ->and($result['validation']['errors'])->toContain('Invalid position: 25 (must be 1-18)');
    });
});

describe('Skill List Extraction', function () {
    it('extracts skill list data successfully', function () {
        $ocrText = <<<'TEXT'
        所持SP: 450
        コーナー回復 SP: 120
        直線一気 SP: 180 習得済
        末脚 SP: 150 ヒントLv: 2
        TEXT;

        $result = $this->service->extractSkillList($ocrText);

        expect($result['success'])->toBeTrue()
            ->and($result['data']['total_sp'])->toBe(450)
            ->and($result['data']['skills'])->toHaveCount(3);
    });

    it('validates SP cost range', function () {
        $ocrText = <<<'TEXT'
        所持SP: 450
        InvalidSkill SP: 999
        TEXT;

        $result = $this->service->extractSkillList($ocrText);

        expect($result['validation']['valid'])->toBeFalse()
            ->and($result['validation']['errors'])->toContain('Skill #0: Invalid SP cost 999 (must be 0-500)');
    });
});

describe('Batch Extraction', function () {
    it('processes multiple screenshots', function () {
        $screenshots = [
            [
                'screen_type' => 'character_stats',
                'ocr_text' => "スピード: 850\nスタミナ: 720",
            ],
            [
                'screen_type' => 'training_session',
                'ocr_text' => "トレーニング: スピード\nスピード +45",
            ],
        ];

        $results = $this->service->batchExtract($screenshots);

        expect($results)->toHaveCount(2)
            ->and($results[0]['screen_type'])->toBe('character_stats')
            ->and($results[1]['screen_type'])->toBe('training_session');
    });
});

describe('Error Handling', function () {
    it('handles invalid screen type gracefully', function () {
        $result = $this->service->extractData('invalid_type', 'test text');

        expect($result['success'])->toBeFalse()
            ->and($result['parse_errors'])->toContain('Unknown screen type: invalid_type');
    });

    it('handles empty OCR text', function () {
        $result = $this->service->extractCharacterStats('');

        expect($result['success'])->toBeFalse()
            ->and($result['confidence'])->toBe(0.0);
    });
});
