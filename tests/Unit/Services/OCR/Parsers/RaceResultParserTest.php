<?php

declare(strict_types=1);

use App\Services\OCR\Parsers\RaceResultParser;

describe('RaceResultParser', function () {
    beforeEach(function () {
        $this->parser = new RaceResultParser;
    });

    it('returns correct screen type', function () {
        expect($this->parser->getScreenType())->toBe('race_result');
    });

    it('parses complete race result', function () {
        $text = <<<'TEXT'
        レース: 日本ダービー
        G1 2400m 芝
        着順: 1位
        ファン +8000
        SP +60
        TEXT;

        $result = $this->parser->parse($text);

        expect($result['success'])->toBeTrue()
            ->and($result['data']['position'])->toBe(1)
            ->and($result['data']['race_grade'])->toBe('G1')
            ->and($result['data']['distance'])->toBe(2400)
            ->and($result['data']['distance_category'])->toBe('medium')
            ->and($result['data']['surface'])->toBe('turf')
            ->and($result['data']['fans_gained'])->toBe(8000)
            ->and($result['data']['skill_points_gained'])->toBe(60)
            ->and($result['data']['outcome'])->toBe('victory');
    });

    it('categorizes distances correctly', function () {
        $text1 = 'レース 1200m';
        $result1 = $this->parser->parse($text1);
        expect($result1['data']['distance_category'])->toBe('sprint');

        $text2 = 'レース 1600m';
        $result2 = $this->parser->parse($text2);
        expect($result2['data']['distance_category'])->toBe('mile');

        $text3 = 'レース 2000m';
        $result3 = $this->parser->parse($text3);
        expect($result3['data']['distance_category'])->toBe('medium');

        $text4 = 'レース 3000m';
        $result4 = $this->parser->parse($text4);
        expect($result4['data']['distance_category'])->toBe('long');
    });

    it('determines race outcomes correctly', function () {
        $text1 = '着順: 1位';
        $result1 = $this->parser->parse($text1);
        expect($result1['data']['outcome'])->toBe('victory');

        $text2 = '着順: 3位';
        $result2 = $this->parser->parse($text2);
        expect($result2['data']['outcome'])->toBe('podium');

        $text3 = '着順: 5位';
        $result3 = $this->parser->parse($text3);
        expect($result3['data']['outcome'])->toBe('top_5');

        $text4 = '着順: 10位';
        $result4 = $this->parser->parse($text4);
        expect($result4['data']['outcome'])->toBe('defeat');
    });

    it('parses dirt surface correctly', function () {
        $text = 'レース ダート 1800m';

        $result = $this->parser->parse($text);

        expect($result['data']['surface'])->toBe('dirt');
    });

    it('validates position correctly', function () {
        $validData = ['position' => 5];
        $result = $this->parser->validate($validData);
        expect($result['valid'])->toBeTrue();

        $invalidData = ['position' => 25]; // Too high
        $result2 = $this->parser->validate($invalidData);
        expect($result2['valid'])->toBeFalse();
    });

    it('parses Pre-OP and OP races', function () {
        $text1 = 'レース Pre-OP';
        $result1 = $this->parser->parse($text1);
        expect($result1['data']['race_grade'])->toBe('Pre-OP');

        $text2 = 'レース OP';
        $result2 = $this->parser->parse($text2);
        expect($result2['data']['race_grade'])->toBe('OP');
    });
});
