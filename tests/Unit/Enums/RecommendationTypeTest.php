<?php

declare(strict_types=1);

use App\Enums\RecommendationType;

describe('RecommendationType Enum', function () {
    it('has all expected cases', function () {
        $cases = RecommendationType::cases();

        expect($cases)->toHaveCount(5);
        expect($cases)->toContain(RecommendationType::TRAINING_FACILITY);
        expect($cases)->toContain(RecommendationType::SKILL_PURCHASE);
        expect($cases)->toContain(RecommendationType::RACE_STRATEGY);
        expect($cases)->toContain(RecommendationType::REST_RECOVERY);
        expect($cases)->toContain(RecommendationType::BOND_BUILDING);
    });

    it('has correct string values', function () {
        expect(RecommendationType::TRAINING_FACILITY->value)->toBe('training_facility');
        expect(RecommendationType::SKILL_PURCHASE->value)->toBe('skill_purchase');
        expect(RecommendationType::RACE_STRATEGY->value)->toBe('race_strategy');
        expect(RecommendationType::REST_RECOVERY->value)->toBe('rest_recovery');
        expect(RecommendationType::BOND_BUILDING->value)->toBe('bond_building');
    });

    describe('label() method', function () {
        it('returns correct labels for all types', function () {
            expect(RecommendationType::TRAINING_FACILITY->label())->toBe('Training Facility');
            expect(RecommendationType::SKILL_PURCHASE->label())->toBe('Skill Purchase');
            expect(RecommendationType::RACE_STRATEGY->label())->toBe('Race Strategy');
            expect(RecommendationType::REST_RECOVERY->label())->toBe('Rest & Recovery');
            expect(RecommendationType::BOND_BUILDING->label())->toBe('Bond Building');
        });

        it('returns non-empty strings for all cases', function () {
            foreach (RecommendationType::cases() as $type) {
                expect($type->label())->not->toBeEmpty();
            }
        });
    });

    describe('description() method', function () {
        it('returns correct descriptions for all types', function () {
            expect(RecommendationType::TRAINING_FACILITY->description())
                ->toBe('Recommendations for which training facility to use');
            expect(RecommendationType::SKILL_PURCHASE->description())
                ->toBe('Recommendations for which skills to purchase');
            expect(RecommendationType::RACE_STRATEGY->description())
                ->toBe('Recommendations for race preparation and strategy');
            expect(RecommendationType::REST_RECOVERY->description())
                ->toBe('Recommendations for rest and energy recovery');
            expect(RecommendationType::BOND_BUILDING->description())
                ->toBe('Recommendations for building support card bonds');
        });

        it('returns non-empty strings for all cases', function () {
            foreach (RecommendationType::cases() as $type) {
                expect($type->description())->not->toBeEmpty();
            }
        });
    });

    describe('icon() method', function () {
        it('returns correct icons for all types', function () {
            expect(RecommendationType::TRAINING_FACILITY->icon())->toBe('facility');
            expect(RecommendationType::SKILL_PURCHASE->icon())->toBe('skill');
            expect(RecommendationType::RACE_STRATEGY->icon())->toBe('race');
            expect(RecommendationType::REST_RECOVERY->icon())->toBe('rest');
            expect(RecommendationType::BOND_BUILDING->icon())->toBe('bond');
        });

        it('returns non-empty strings for all cases', function () {
            foreach (RecommendationType::cases() as $type) {
                expect($type->icon())->not->toBeEmpty();
            }
        });
    });

    describe('isTrainingRelated() method', function () {
        it('returns true for training-related types', function () {
            expect(RecommendationType::TRAINING_FACILITY->isTrainingRelated())->toBeTrue();
            expect(RecommendationType::REST_RECOVERY->isTrainingRelated())->toBeTrue();
            expect(RecommendationType::BOND_BUILDING->isTrainingRelated())->toBeTrue();
        });

        it('returns false for non-training-related types', function () {
            expect(RecommendationType::SKILL_PURCHASE->isTrainingRelated())->toBeFalse();
            expect(RecommendationType::RACE_STRATEGY->isTrainingRelated())->toBeFalse();
        });
    });

    describe('isStrategic() method', function () {
        it('returns true for strategic types', function () {
            expect(RecommendationType::SKILL_PURCHASE->isStrategic())->toBeTrue();
            expect(RecommendationType::RACE_STRATEGY->isStrategic())->toBeTrue();
        });

        it('returns false for non-strategic types', function () {
            expect(RecommendationType::TRAINING_FACILITY->isStrategic())->toBeFalse();
            expect(RecommendationType::REST_RECOVERY->isStrategic())->toBeFalse();
            expect(RecommendationType::BOND_BUILDING->isStrategic())->toBeFalse();
        });
    });

    describe('all() static method', function () {
        it('returns all cases', function () {
            $all = RecommendationType::all();

            expect($all)->toBeArray();
            expect($all)->toHaveCount(5);
            expect($all)->toBe(RecommendationType::cases());
        });
    });

    describe('values() static method', function () {
        it('returns all string values', function () {
            $values = RecommendationType::values();

            expect($values)->toBeArray();
            expect($values)->toHaveCount(5);
            expect($values)->toContain('training_facility');
            expect($values)->toContain('skill_purchase');
            expect($values)->toContain('race_strategy');
            expect($values)->toContain('rest_recovery');
            expect($values)->toContain('bond_building');
        });

        it('returns only strings', function () {
            $values = RecommendationType::values();

            foreach ($values as $value) {
                expect($value)->toBeString();
            }
        });
    });

    describe('tryFrom() static method', function () {
        it('creates instance from valid string', function () {
            expect(RecommendationType::tryFrom('training_facility'))
                ->toBe(RecommendationType::TRAINING_FACILITY);
            expect(RecommendationType::tryFrom('skill_purchase'))
                ->toBe(RecommendationType::SKILL_PURCHASE);
            expect(RecommendationType::tryFrom('race_strategy'))
                ->toBe(RecommendationType::RACE_STRATEGY);
            expect(RecommendationType::tryFrom('rest_recovery'))
                ->toBe(RecommendationType::REST_RECOVERY);
            expect(RecommendationType::tryFrom('bond_building'))
                ->toBe(RecommendationType::BOND_BUILDING);
        });

        it('returns null for invalid string', function () {
            expect(RecommendationType::tryFrom('invalid'))->toBeNull();
            expect(RecommendationType::tryFrom(''))->toBeNull();
            expect(RecommendationType::tryFrom('TRAINING_FACILITY'))->toBeNull();
        });
    });

    describe('from() static method', function () {
        it('creates instance from valid string', function () {
            expect(RecommendationType::from('training_facility'))
                ->toBe(RecommendationType::TRAINING_FACILITY);
        });

        it('throws exception for invalid string', function () {
            RecommendationType::from('invalid');
        })->throws(ValueError::class);
    });

    describe('serialization', function () {
        it('can be serialized to JSON', function () {
            $type = RecommendationType::TRAINING_FACILITY;
            $json = json_encode(['type' => $type]);

            expect($json)->toBe('{"type":"training_facility"}');
        });

        it('can be deserialized from JSON', function () {
            $json = '{"type":"skill_purchase"}';
            $data = json_decode($json, true);
            $type = RecommendationType::from($data['type']);

            expect($type)->toBe(RecommendationType::SKILL_PURCHASE);
        });
    });

    describe('comparison', function () {
        it('can be compared with === operator', function () {
            $type1 = RecommendationType::TRAINING_FACILITY;
            $type2 = RecommendationType::TRAINING_FACILITY;
            $type3 = RecommendationType::SKILL_PURCHASE;

            expect($type1 === $type2)->toBeTrue(); // @phpstan-ignore identical.alwaysTrue
            expect($type1 === $type3)->toBeFalse(); // @phpstan-ignore identical.alwaysFalse
        });

        it('can be compared with == operator', function () {
            $type1 = RecommendationType::TRAINING_FACILITY;
            $type2 = RecommendationType::TRAINING_FACILITY;
            $type3 = RecommendationType::SKILL_PURCHASE;

            expect($type1 == $type2)->toBeTrue(); // @phpstan-ignore equal.alwaysTrue
            expect($type1 == $type3)->toBeFalse(); // @phpstan-ignore equal.alwaysFalse
        });
    });

    describe('type safety', function () {
        it('enforces type in function parameters', function () {
            $testFunction = function (RecommendationType $type): string {
                return $type->value;
            };

            expect($testFunction(RecommendationType::TRAINING_FACILITY))
                ->toBe('training_facility');
        });

        it('can be used in match expressions', function () {
            $type = RecommendationType::SKILL_PURCHASE;

            $result = match ($type) {
                RecommendationType::TRAINING_FACILITY => 'facility', // @phpstan-ignore match.alwaysFalse
                RecommendationType::SKILL_PURCHASE => 'skill', // @phpstan-ignore match.alwaysTrue
                RecommendationType::RACE_STRATEGY => 'race',
                RecommendationType::REST_RECOVERY => 'rest',
                RecommendationType::BOND_BUILDING => 'bond',
            };

            expect($result)->toBe('skill');
        });
    });

    describe('edge cases', function () {
        it('handles all cases in loops', function () {
            $count = 0;
            foreach (RecommendationType::cases() as $type) {
                expect($type)->toBeInstanceOf(RecommendationType::class);
                $count++;
            }

            expect($count)->toBe(5);
        });

        it('maintains consistency between all() and cases()', function () {
            expect(RecommendationType::all())->toBe(RecommendationType::cases());
        });

        it('maintains consistency between values() and case values', function () {
            $values = RecommendationType::values();
            $caseValues = array_map(fn ($case) => $case->value, RecommendationType::cases());

            expect($values)->toBe($caseValues);
        });
    });
});
