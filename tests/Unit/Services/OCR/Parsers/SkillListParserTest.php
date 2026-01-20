<?php

declare(strict_types=1);

use App\Services\OCR\Parsers\SkillListParser;

describe('SkillListParser', function () {
    beforeEach(function () {
        $this->parser = new SkillListParser;
    });

    it('returns correct screen type', function () {
        expect($this->parser->getScreenType())->toBe('skill_list');
    });

    it('parses skill list with multiple skills', function () {
        $text = <<<'TEXT'
        所持SP: 450
        スキル一覧
        加速力 SP: 120 ヒント Lv2
        回復力 SP: 150 習得済
        パッシブ力 SP: 180
        TEXT;

        $result = $this->parser->parse($text);

        expect($result['success'])->toBeTrue()
            ->and($result['data']['total_sp'])->toBe(450)
            ->and($result['data']['skills'])->toBeArray()
            ->and($result['data']['skill_count'])->toBeGreaterThan(0);

        // Find the skill with hint level
        $skillWithHint = collect($result['data']['skills'])->first(fn ($skill) => isset($skill['hint_level']));
        if ($skillWithHint) {
            expect($skillWithHint['hint_level'])->toBe(2);
        }

        // Find the acquired skill
        $acquiredSkill = collect($result['data']['skills'])->first(fn ($skill) => $skill['is_acquired'] === true);
        expect($acquiredSkill)->not->toBeNull();
    });

    it('determines skill types correctly', function () {
        $text = <<<'TEXT'
        加速力 SP: 120
        回復力 SP: 150
        パッシブ力 SP: 180
        デバフ力 SP: 140
        TEXT;

        $result = $this->parser->parse($text);

        expect($result['data']['skills'][0]['skill_type'])->toBe('speed')
            ->and($result['data']['skills'][1]['skill_type'])->toBe('recovery')
            ->and($result['data']['skills'][2]['skill_type'])->toBe('passive')
            ->and($result['data']['skills'][3]['skill_type'])->toBe('debuff');
    });

    it('validates total SP correctly', function () {
        $validData = ['total_sp' => 500];
        $result = $this->parser->validate($validData);
        expect($result['valid'])->toBeTrue();

        $invalidData = ['total_sp' => -100]; // Negative
        $result2 = $this->parser->validate($invalidData);
        expect($result2['valid'])->toBeFalse();
    });

    it('validates skill SP costs', function () {
        $invalidData = [
            'skills' => [
                ['name' => 'Test Skill', 'sp_cost' => 600], // Too high
            ],
        ];

        $result = $this->parser->validate($invalidData);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty();
    });

    it('handles skills without hint levels', function () {
        $text = 'スキル SP: 120';

        $result = $this->parser->parse($text);

        expect($result['data']['skills'][0])->not->toHaveKey('hint_level');
    });
});
