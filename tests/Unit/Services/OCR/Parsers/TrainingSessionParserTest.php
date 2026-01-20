<?php

declare(strict_types=1);

use App\Services\OCR\Parsers\TrainingSessionParser;

describe('TrainingSessionParser', function () {
    beforeEach(function () {
        $this->parser = new TrainingSessionParser;
    });

    it('returns correct screen type', function () {
        expect($this->parser->getScreenType())->toBe('training_session');
    });

    it('parses training session with stat gains', function () {
        $text = <<<'TEXT'
        トレーニング: スピード
        スピード +45
        パワー +15
        体力 -20
        スキルヒント
        友情トレーニング
        TEXT;

        $result = $this->parser->parse($text);

        expect($result['success'])->toBeTrue()
            ->and($result['data']['training_type'])->toBe('speed')
            ->and($result['data']['stat_gains'])->toHaveKey('speed')
            ->and($result['data']['stat_gains']['speed'])->toBe(45)
            ->and($result['data']['stat_gains']['power'])->toBe(15)
            ->and($result['data']['energy_cost'])->toBe(20)
            ->and($result['data']['has_skill_hint'])->toBeTrue()
            ->and($result['data']['has_friendship'])->toBeTrue();
    });

    it('detects Spirit Burst indicator', function () {
        $text = 'トレーニング スピリットバースト 炎';

        $result = $this->parser->parse($text);

        expect($result['data']['has_spirit_burst'])->toBeTrue();
    });

    it('parses English training types', function () {
        $text = 'Training: Speed Speed +40 Energy -15';

        $result = $this->parser->parse($text);

        expect($result['data']['training_type'])->toBe('speed')
            ->and($result['data']['stat_gains']['speed'])->toBe(40);
    });

    it('validates training type correctly', function () {
        $validData = [
            'training_type' => 'speed',
            'stat_gains' => ['speed' => 45],
            'energy_cost' => 20,
        ];

        $result = $this->parser->validate($validData);

        expect($result['valid'])->toBeTrue();
    });

    it('rejects invalid stat gains', function () {
        $invalidData = [
            'training_type' => 'speed',
            'stat_gains' => ['speed' => 250], // Too high
        ];

        $result = $this->parser->validate($invalidData);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty();
    });

    it('handles rest and infirmary training types', function () {
        $text1 = 'トレーニング: 休息';
        $result1 = $this->parser->parse($text1);
        expect($result1['data']['training_type'])->toBe('rest');

        $text2 = 'トレーニング: 保健室';
        $result2 = $this->parser->parse($text2);
        expect($result2['data']['training_type'])->toBe('infirmary');
    });
});
