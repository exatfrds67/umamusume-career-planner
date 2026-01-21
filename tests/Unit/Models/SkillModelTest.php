<?php

declare(strict_types=1);

use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillHint;

describe('Skill Model', function (): void {
    describe('relationships', function (): void {
        it('has many skill hints', function (): void {
            $skill = Skill::factory()->create();

            SkillHint::factory()->count(3)->create(['skill_id' => $skill->id]);

            expect($skill->skillHints)->toHaveCount(3);
            expect($skill->skillHints->first())->toBeInstanceOf(SkillHint::class);
        });

        it('has many skill acquisitions', function (): void {
            $skill = Skill::factory()->create();

            SkillAcquisition::factory()->count(5)->create(['skill_id' => $skill->id]);

            expect($skill->skillAcquisitions)->toHaveCount(5);
            expect($skill->skillAcquisitions->first())->toBeInstanceOf(SkillAcquisition::class);
        });
    });

    describe('attributes', function (): void {
        it('has valid skill type', function (): void {
            $skill = Skill::factory()->create(['skill_type' => 'normal']);

            expect($skill->skill_type)->toBeIn(['normal', 'rare', 'unique', 'inherited']);
        });

        it('has valid SP cost', function (): void {
            $skill = Skill::factory()->create(['base_sp_cost' => 120]);

            expect($skill->base_sp_cost)->toBeInt()->toBeGreaterThan(0);
        });

        it('has name and description', function (): void {
            $skill = Skill::factory()->create([
                'name' => 'Speed Boost',
                'description' => 'Increases speed during race',
            ]);

            expect($skill->name)->toBe('Speed Boost');
            expect($skill->description)->not->toBeEmpty();
        });
    });

    describe('scopes', function (): void {
        it('filters by skill type', function (): void {
            Skill::factory()->count(3)->create(['skill_type' => 'normal']);
            Skill::factory()->count(2)->create(['skill_type' => 'rare']);
            Skill::factory()->count(1)->create(['skill_type' => 'unique']);

            $normalSkills = Skill::where('skill_type', 'normal')->get();
            $rareSkills = Skill::where('skill_type', 'rare')->get();

            expect($normalSkills)->toHaveCount(3);
            expect($rareSkills)->toHaveCount(2);
        });

        it('filters active skills', function (): void {
            Skill::factory()->count(5)->create(['is_active' => true]);
            Skill::factory()->count(2)->create(['is_active' => false]);

            $activeSkills = Skill::where('is_active', true)->get();

            expect($activeSkills)->toHaveCount(5);
        });
    });

    describe('factory', function (): void {
        it('creates valid skill with factory', function (): void {
            $skill = Skill::factory()->create();

            expect($skill)->toBeInstanceOf(Skill::class);
            expect($skill->name)->not->toBeEmpty();
            expect($skill->base_sp_cost)->toBeGreaterThan(0);
        });

        it('creates skill with specific type', function (): void {
            $skill = Skill::factory()->create(['skill_type' => 'unique']);

            expect($skill->skill_type)->toBe('unique');
        });
    });
});

describe('SkillHint Model', function (): void {
    describe('relationships', function (): void {
        it('belongs to a skill', function (): void {
            $skill = Skill::factory()->create();
            $hint = SkillHint::factory()->create(['skill_id' => $skill->id]);

            expect($hint->skill)->toBeInstanceOf(Skill::class);
            expect($hint->skill->id)->toBe($skill->id);
        });
    });

    describe('attributes', function (): void {
        it('has discount percentage', function (): void {
            $skill = Skill::factory()->create();
            $hint = SkillHint::factory()->create([
                'skill_id' => $skill->id,
                'discount_percentage' => 30,
            ]);

            expect($hint->discount_percentage)->toBe(30);
            expect($hint->discount_percentage)->toBeGreaterThanOrEqual(0);
            expect($hint->discount_percentage)->toBeLessThanOrEqual(100);
        });
    });
});

describe('SkillAcquisition Model', function (): void {
    describe('relationships', function (): void {
        it('belongs to a skill', function (): void {
            $skill = Skill::factory()->create();
            $acquisition = SkillAcquisition::factory()->create(['skill_id' => $skill->id]);

            expect($acquisition->skill)->toBeInstanceOf(Skill::class);
            expect($acquisition->skill->id)->toBe($skill->id);
        });
    });

    describe('attributes', function (): void {
        it('tracks SP spent', function (): void {
            $skill = Skill::factory()->create();
            $acquisition = SkillAcquisition::factory()->create([
                'skill_id' => $skill->id,
                'sp_spent' => 100,
            ]);

            expect($acquisition->sp_spent)->toBe(100);
        });

        it('tracks acquisition turn', function (): void {
            $skill = Skill::factory()->create();
            $acquisition = SkillAcquisition::factory()->create([
                'skill_id' => $skill->id,
                'acquired_at_turn' => 25,
            ]);

            expect($acquisition->acquired_at_turn)->toBe(25);
        });
    });
});
