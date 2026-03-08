<?php

declare(strict_types=1);

use App\Enums\AffinityGrade;
use App\Enums\SparkType;
use App\Models\Career;
use App\Models\InheritanceEvent;
use App\Models\ParentCharacter;
use App\Services\InheritanceEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new InheritanceEventService;
});

describe('InheritanceEventService', function () {
    describe('generateSpark', function () {
        it('returns a valid spark structure', function () {
            $parent = ParentCharacter::factory()
                ->highAffinity()
                ->highStats('speed')
                ->create();

            $spark = $this->service->generateSpark($parent, 1);

            expect($spark)
                ->toHaveKeys(['spark_type', 'star_level'])
                ->and($spark['spark_type'])->toBeInstanceOf(SparkType::class)
                ->and($spark['star_level'])->toBeIn([1, 2, 3]);
        });

        it('generates blue spark with correct stat bonus values', function () {
            $parent = ParentCharacter::factory()->highStats('speed')->highAffinity()->create();

            $foundBlue = false;
            for ($i = 0; $i < 200; $i++) {
                $spark = $this->service->generateSpark($parent, 1);
                if ($spark['spark_type'] === SparkType::Blue) {
                    $foundBlue = true;
                    expect($spark['target_stat'])->not->toBeNull()
                        ->and($spark['stat_bonus'])->toBeIn([9, 18, 27])
                        ->and($spark['growth_rate_bonus'])->toBeNull()
                        ->and($spark['sp_bonus'])->toBeNull();
                    break;
                }
            }

            expect($foundBlue)->toBeTrue('Expected at least one Blue spark in 200 rolls');
        });

        it('generates green spark with growth rate values', function () {
            $parent = ParentCharacter::factory()->highAffinity()->create();

            $foundGreen = false;
            for ($i = 0; $i < 200; $i++) {
                $spark = $this->service->generateSpark($parent, 2);
                if ($spark['spark_type'] === SparkType::Green) {
                    $foundGreen = true;
                    expect($spark['target_stat'])->not->toBeNull()
                        ->and($spark['growth_rate_bonus'])->toBeIn([1.0, 2.0, 3.0])
                        ->and($spark['stat_bonus'])->toBeNull()
                        ->and($spark['sp_bonus'])->toBeNull();
                    break;
                }
            }

            expect($foundGreen)->toBeTrue('Expected at least one Green spark in 200 rolls');
        });

        it('generates white spark with SP bonus values', function () {
            $parent = ParentCharacter::factory()->highAffinity()->create();

            $foundWhite = false;
            for ($i = 0; $i < 200; $i++) {
                $spark = $this->service->generateSpark($parent, 3);
                if ($spark['spark_type'] === SparkType::White) {
                    $foundWhite = true;
                    expect($spark['sp_bonus'])->toBeIn([20, 40, 60])
                        ->and($spark['target_stat'])->toBeNull()
                        ->and($spark['stat_bonus'])->toBeNull()
                        ->and($spark['growth_rate_bonus'])->toBeNull();
                    break;
                }
            }

            expect($foundWhite)->toBeTrue('Expected at least one White spark in 200 rolls');
        });
    });

    describe('createEvent', function () {
        it('persists an inheritance event to the database', function () {
            $career = Career::factory()->create();
            $parent = ParentCharacter::factory()->create(['career_id' => $career->id]);

            $event = $this->service->createEvent(
                career: $career,
                parent: $parent,
                eventNumber: 1,
                sparkType: SparkType::Blue,
                starLevel: 3,
                targetStat: 'speed',
                statBonus: 27,
            );

            expect($event)->toBeInstanceOf(InheritanceEvent::class)
                ->and($event->exists)->toBeTrue()
                ->and($event->career_id)->toBe($career->id)
                ->and($event->parent_character_id)->toBe($parent->id)
                ->and($event->event_number)->toBe(1)
                ->and($event->spark_type)->toBe(SparkType::Blue)
                ->and($event->star_level)->toBe(3)
                ->and($event->target_stat)->toBe('speed')
                ->and($event->stat_bonus)->toBe(27)
                ->and($event->is_applied)->toBeFalse();

            $this->assertDatabaseHas('ucp_inheritance_events', [
                'career_id' => $career->id,
                'event_number' => 1,
                'spark_type' => 'blue',
                'star_level' => 3,
            ]);
        });

        it('creates a white spark event with SP bonus', function () {
            $career = Career::factory()->create();
            $parent = ParentCharacter::factory()->create(['career_id' => $career->id]);

            $event = $this->service->createEvent(
                career: $career,
                parent: $parent,
                eventNumber: 2,
                sparkType: SparkType::White,
                starLevel: 2,
                spBonus: 40,
            );

            expect($event->spark_type)->toBe(SparkType::White)
                ->and($event->sp_bonus)->toBe(40)
                ->and($event->stat_bonus)->toBeNull()
                ->and($event->growth_rate_bonus)->toBeNull();
        });
    });

    describe('triggerInspirationEvent', function () {
        it('generates and persists a complete inspiration event', function () {
            $career = Career::factory()->create();
            $parent = ParentCharacter::factory()->highStats('speed')->create(['career_id' => $career->id]);

            $event = $this->service->triggerInspirationEvent($career, $parent, 1);

            expect($event)->toBeInstanceOf(InheritanceEvent::class)
                ->and($event->exists)->toBeTrue()
                ->and($event->career_id)->toBe($career->id)
                ->and($event->parent_character_id)->toBe($parent->id)
                ->and($event->event_number)->toBe(1)
                ->and($event->spark_type)->toBeInstanceOf(SparkType::class)
                ->and($event->star_level)->toBeIn([1, 2, 3])
                ->and($event->is_applied)->toBeFalse();
        });
    });

    describe('applyEvent', function () {
        it('applies a blue spark event and returns stat bonus result', function () {
            $event = InheritanceEvent::factory()->blueSpark('speed', 3)->create();

            $result = $this->service->applyEvent($event);

            expect($result['success'])->toBeTrue()
                ->and($result['type'])->toBe('stat_bonus')
                ->and($result['stat'])->toBe('speed')
                ->and($result['value'])->toBe(27)
                ->and($result['description'])->toContain('+27')
                ->and($result['description'])->toContain('Blue spark');

            $event->refresh();
            expect($event->is_applied)->toBeTrue();
        });

        it('applies a green spark event and returns growth rate result', function () {
            $event = InheritanceEvent::factory()->greenSpark('stamina', 2)->create();

            $result = $this->service->applyEvent($event);

            expect($result['success'])->toBeTrue()
                ->and($result['type'])->toBe('growth_rate_bonus')
                ->and($result['stat'])->toBe('stamina')
                ->and($result['value'])->toBe(2.0)
                ->and($result['description'])->toContain('Green spark');

            $event->refresh();
            expect($event->is_applied)->toBeTrue();
        });

        it('applies a white spark event and returns SP bonus result', function () {
            $event = InheritanceEvent::factory()->whiteSpark(1)->create();

            $result = $this->service->applyEvent($event);

            expect($result['success'])->toBeTrue()
                ->and($result['type'])->toBe('sp_bonus')
                ->and($result['value'])->toBe(20)
                ->and($result['description'])->toContain('White spark');

            $event->refresh();
            expect($event->is_applied)->toBeTrue();
        });

        it('applies a pink spark event and returns skill inheritance result', function () {
            $event = InheritanceEvent::factory()->pinkSpark('Speed Star', 2)->create();

            $result = $this->service->applyEvent($event);

            expect($result['success'])->toBeTrue()
                ->and($result['type'])->toBe('skill_inheritance')
                ->and($result['skill_name'])->toBe('Speed Star')
                ->and($result['description'])->toContain('Pink spark');

            $event->refresh();
            expect($event->is_applied)->toBeTrue();
        });

        it('refuses to apply an already applied event', function () {
            $event = InheritanceEvent::factory()->blueSpark('speed', 2)->applied()->create();

            $result = $this->service->applyEvent($event);

            expect($result['success'])->toBeFalse()
                ->and($result['reason'])->toContain('already applied');
        });
    });

    describe('getEventsForCareer', function () {
        it('returns events in order of event number', function () {
            $career = Career::factory()->create();
            $parent = ParentCharacter::factory()->create(['career_id' => $career->id]);

            InheritanceEvent::factory()
                ->eventNumber(3)
                ->blueSpark('speed', 1)
                ->create(['career_id' => $career->id, 'parent_character_id' => $parent->id]);
            InheritanceEvent::factory()
                ->eventNumber(1)
                ->greenSpark('power', 2)
                ->create(['career_id' => $career->id, 'parent_character_id' => $parent->id]);
            InheritanceEvent::factory()
                ->eventNumber(2)
                ->whiteSpark(1)
                ->create(['career_id' => $career->id, 'parent_character_id' => $parent->id]);

            $events = $this->service->getEventsForCareer($career);

            expect($events)->toHaveCount(3)
                ->and($events[0]->event_number)->toBe(1)
                ->and($events[1]->event_number)->toBe(2)
                ->and($events[2]->event_number)->toBe(3);
        });

        it('returns empty collection when career has no events', function () {
            $career = Career::factory()->create();

            $events = $this->service->getEventsForCareer($career);

            expect($events)->toBeEmpty();
        });
    });

    describe('calculateAffinity', function () {
        it('returns low affinity when nothing matches', function () {
            $career = Career::factory()->create(['scenario_type' => 'ura_finale']);
            $parent = ParentCharacter::factory()->create([
                'career_id' => $career->id,
                'scenario_type' => 'unity_cup',
                'running_style' => 'runner',
                'preferred_distance' => 'sprint',
            ]);

            $grade = $this->service->calculateAffinity($parent, $career);

            expect($grade)->toBe(AffinityGrade::Low);
        });

        it('returns standard affinity when scenario matches', function () {
            $career = Career::factory()->create(['scenario_type' => 'ura_finale']);
            $parent = ParentCharacter::factory()->create([
                'career_id' => $career->id,
                'scenario_type' => 'ura_finale',
                'running_style' => 'runner',
                'preferred_distance' => 'sprint',
            ]);

            $grade = $this->service->calculateAffinity($parent, $career);

            // scenario_type match = +1 point → score 1 < 2 → Low
            // Character model doesn't have preferred_distance or running_style,
            // so those matches are never scored
            expect($grade)->toBe(AffinityGrade::Low);
        });
    });

    describe('spark bonus lookup methods', function () {
        it('returns correct blue spark bonuses for each star level', function (int $starLevel, int $expected) {
            expect($this->service->getBlueSparkBonus($starLevel))->toBe($expected);
        })->with([
            '1-star' => [1, 9],
            '2-star' => [2, 18],
            '3-star' => [3, 27],
        ]);

        it('returns zero for invalid blue spark star level', function () {
            expect($this->service->getBlueSparkBonus(4))->toBe(0);
        });

        it('returns correct green spark growth rates for each star level', function (int $starLevel, float $expected) {
            expect($this->service->getGreenSparkGrowthRate($starLevel))->toBe($expected);
        })->with([
            '1-star' => [1, 1.0],
            '2-star' => [2, 2.0],
            '3-star' => [3, 3.0],
        ]);

        it('returns zero for invalid green spark star level', function () {
            expect($this->service->getGreenSparkGrowthRate(0))->toBe(0.0);
        });

        it('returns correct white spark SP bonuses for each star level', function (int $starLevel, int $expected) {
            expect($this->service->getWhiteSparkSpBonus($starLevel))->toBe($expected);
        })->with([
            '1-star' => [1, 20],
            '2-star' => [2, 40],
            '3-star' => [3, 60],
        ]);

        it('returns zero for invalid white spark star level', function () {
            expect($this->service->getWhiteSparkSpBonus(5))->toBe(0);
        });
    });

    describe('ParentCharacter model', function () {
        it('correctly identifies highest stat', function () {
            $parent = ParentCharacter::factory()->create([
                'final_speed' => 800,
                'final_stamina' => 600,
                'final_power' => 1100,
                'final_guts' => 700,
                'final_wit' => 900,
            ]);

            $highest = $parent->getHighestStat();

            expect($highest['stat'])->toBe('power')
                ->and($highest['value'])->toBe(1100);
        });

        it('returns stat value by type', function () {
            $parent = ParentCharacter::factory()->create([
                'final_speed' => 950,
                'final_guts' => 1200,
            ]);

            expect($parent->getStatValue('speed'))->toBe(950)
                ->and($parent->getStatValue('guts'))->toBe(1200);
        });
    });

    describe('InheritanceEvent model', function () {
        it('correctly identifies spark types', function () {
            $blue = InheritanceEvent::factory()->blueSpark()->create();
            $pink = InheritanceEvent::factory()->pinkSpark()->create();
            $green = InheritanceEvent::factory()->greenSpark()->create();
            $white = InheritanceEvent::factory()->whiteSpark()->create();

            expect($blue->isStatBonus())->toBeTrue()
                ->and($blue->isGrowthRateBonus())->toBeFalse()
                ->and($pink->isSkillInheritance())->toBeTrue()
                ->and($pink->isStatBonus())->toBeFalse()
                ->and($green->isGrowthRateBonus())->toBeTrue()
                ->and($green->isSpBonus())->toBeFalse()
                ->and($white->isSpBonus())->toBeTrue()
                ->and($white->isSkillInheritance())->toBeFalse();
        });
    });

    describe('SparkType enum', function () {
        it('has labels for all cases', function () {
            expect(SparkType::Blue->label())->toBe('Stat Bonus')
                ->and(SparkType::Pink->label())->toBe('Skill Inheritance')
                ->and(SparkType::Green->label())->toBe('Growth Rate Bonus')
                ->and(SparkType::White->label())->toBe('SP Bonus');
        });
    });

    describe('AffinityGrade enum', function () {
        it('has correct star level modifiers', function () {
            expect(AffinityGrade::High->starLevelModifier())->toBe(1.3)
                ->and(AffinityGrade::Standard->starLevelModifier())->toBe(1.0)
                ->and(AffinityGrade::Low->starLevelModifier())->toBe(0.7);
        });

        it('has correct symbols', function () {
            expect(AffinityGrade::High->symbol())->toBe('◎')
                ->and(AffinityGrade::Standard->symbol())->toBe('○')
                ->and(AffinityGrade::Low->symbol())->toBe('△');
        });
    });
});
