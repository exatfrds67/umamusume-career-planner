<?php

declare(strict_types=1);

use App\Services\OCR\Parsers\CharacterStatsParser;

describe('CharacterStatsParser', function () {
    beforeEach(function () {
        $this->parser = new CharacterStatsParser;
    });

    it('returns correct screen type', function () {
        expect($this->parser->getScreenType())->toBe('character_stats');
    });

    it('parses complete character stats successfully', function () {
        $text = <<<'TEXT'
        ウマ娘: サイレンススズカ
        スピード: 850
        スタミナ: 720
        パワー: 680
        根性: 450
        賢さ: 600
        ターン: 45/72
        体力: 75
        やる気: 好調
        TEXT;

        $result = $this->parser->parse($text);

        expect($result['success'])->toBeTrue()
            ->and($result['confidence'])->toBeGreaterThan(0.8)
            ->and($result['data']['stats'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit'])
            ->and($result['data']['stats']['speed'])->toBe(850)
            ->and($result['data']['stats']['stamina'])->toBe(720)
            ->and($result['data']['stats']['power'])->toBe(680)
            ->and($result['data']['stats']['guts'])->toBe(450)
            ->and($result['data']['stats']['wit'])->toBe(600)
            ->and($result['data']['current_turn'])->toBe(45)
            ->and($result['data']['total_turns'])->toBe(72)
            ->and($result['data']['energy_level'])->toBe(75)
            ->and($result['data']['mood_status'])->toBe('good');
    });

    it('parses English stat names', function () {
        $text = 'Speed: 900 Stamina: 800 Power: 700 Guts: 500 Wit: 650';

        $result = $this->parser->parse($text);

        expect($result['success'])->toBeTrue()
            ->and($result['data']['stats']['speed'])->toBe(900)
            ->and($result['data']['stats']['stamina'])->toBe(800);
    });

    it('handles partial stats extraction', function () {
        $text = 'スピード: 850 スタミナ: 720';

        $result = $this->parser->parse($text);

        expect($result['success'])->toBeTrue() // 40% confidence meets threshold
            ->and($result['confidence'])->toBe(0.4)
            ->and($result['data']['stats']['speed'])->toBe(850)
            ->and($result['data']['stats']['stamina'])->toBe(720)
            ->and($result['data']['stats']['power'])->toBeNull();
    });

    it('validates stat values correctly', function () {
        $validData = [
            'stats' => [
                'speed' => 850,
                'stamina' => 720,
                'power' => 680,
                'guts' => 450,
                'wit' => 600,
            ],
        ];

        $result = $this->parser->validate($validData);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('rejects invalid stat values', function () {
        $invalidData = [
            'stats' => [
                'speed' => 1500, // Too high
                'stamina' => -100, // Negative
                'power' => 680,
                'guts' => 450,
                'wit' => 600,
            ],
        ];

        $result = $this->parser->validate($invalidData);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty();
    });

    it('normalizes Japanese mood values', function () {
        $text = 'やる気: 絶好調';

        $result = $this->parser->parse($text);

        expect($result['data']['mood_status'])->toBe('great');
    });

    it('normalizes English mood values', function () {
        $text = 'Mood: Good';

        $result = $this->parser->parse($text);

        expect($result['data']['mood_status'])->toBe('good');
    });

    it('calculates confidence based on stats found', function () {
        // All 5 stats found = 100% confidence
        $text = 'Speed: 850 Stamina: 720 Power: 680 Guts: 450 Wit: 600';
        $result = $this->parser->parse($text);
        expect($result['confidence'])->toBe(1.0);

        // 3 stats found = 60% confidence
        $text2 = 'Speed: 850 Stamina: 720 Power: 680';
        $result2 = $this->parser->parse($text2);
        expect($result2['confidence'])->toBe(0.6);
    });
});
