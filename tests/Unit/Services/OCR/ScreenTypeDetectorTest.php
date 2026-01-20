<?php

declare(strict_types=1);

use App\Services\OCR\ScreenTypeDetector;

describe('ScreenTypeDetector', function () {
    beforeEach(function () {
        $this->detector = new ScreenTypeDetector;
    });

    it('detects character stats screen correctly', function () {
        $text = 'スピード: 850 スタミナ: 720 パワー: 680 根性: 450 賢さ: 600';

        $result = $this->detector->detectScreenType($text);

        expect($result['detected_type'])->toBe('character_stats')
            ->and($result['confidence'])->toBeGreaterThanOrEqual(0.5);
    });

    it('detects training session screen correctly', function () {
        $text = 'トレーニング スピード +45 体力 -20 友情トレーニング';

        $result = $this->detector->detectScreenType($text);

        expect($result['detected_type'])->toBe('training_session')
            ->and($result['confidence'])->toBeGreaterThan(0.3);
    });

    it('detects race result screen correctly', function () {
        $text = 'レース結果 着順: 1位 ファン +5000 SP +45';

        $result = $this->detector->detectScreenType($text);

        expect($result['detected_type'])->toBe('race_result')
            ->and($result['confidence'])->toBeGreaterThanOrEqual(0.3);
    });

    it('detects skill list screen correctly', function () {
        $text = 'スキル一覧 SP: 450 スキルヒント Lv3 習得済';

        $result = $this->detector->detectScreenType($text);

        expect($result['detected_type'])->toBe('skill_list')
            ->and($result['confidence'])->toBeGreaterThan(0.3);
    });

    it('returns null for unrecognizable text', function () {
        $text = 'Random text with no game-related keywords';

        $result = $this->detector->detectScreenType($text);

        expect($result['detected_type'])->toBeNull()
            ->and($result['confidence'])->toBeLessThan(0.3);
    });

    it('validates screen type correctly', function () {
        $text = 'スピード: 850 スタミナ: 720 パワー: 680 根性: 450 賢さ: 600';

        $isValid = $this->detector->validateScreenType($text, 'character_stats');

        expect($isValid)->toBeTrue();
    });

    it('returns false for mismatched screen type', function () {
        $text = 'スピード: 850 スタミナ: 720 パワー: 680';

        $isValid = $this->detector->validateScreenType($text, 'race_result');

        expect($isValid)->toBeFalse();
    });

    it('returns all supported types', function () {
        $types = $this->detector->getSupportedTypes();

        expect($types)->toBeArray()
            ->and($types)->toContain('character_stats', 'training_session', 'race_result', 'skill_list');
    });
});
