<?php

declare(strict_types=1);

use App\Enums\Priority;

describe('Priority Enum', function () {
    describe('cases', function () {
        it('has all expected priority levels', function () {
            $cases = Priority::cases();

            expect($cases)->toHaveCount(4);
            expect($cases)->toContain(Priority::CRITICAL);
            expect($cases)->toContain(Priority::HIGH);
            expect($cases)->toContain(Priority::MEDIUM);
            expect($cases)->toContain(Priority::LOW);
        });

        it('has correct string values', function () {
            expect(Priority::CRITICAL->value)->toBe('critical');
            expect(Priority::HIGH->value)->toBe('high');
            expect(Priority::MEDIUM->value)->toBe('medium');
            expect(Priority::LOW->value)->toBe('low');
        });
    });

    describe('label', function () {
        it('returns correct labels', function () {
            expect(Priority::CRITICAL->label())->toBe('Critical');
            expect(Priority::HIGH->label())->toBe('High');
            expect(Priority::MEDIUM->label())->toBe('Medium');
            expect(Priority::LOW->label())->toBe('Low');
        });
    });

    describe('description', function () {
        it('returns correct descriptions', function () {
            expect(Priority::CRITICAL->description())->toBe('Requires immediate attention');
            expect(Priority::HIGH->description())->toBe('Should be addressed soon');
            expect(Priority::MEDIUM->description())->toBe('Helpful but not urgent');
            expect(Priority::LOW->description())->toBe('Optional optimization');
        });
    });

    describe('color', function () {
        it('returns correct color classes', function () {
            expect(Priority::CRITICAL->color())->toBe('red');
            expect(Priority::HIGH->color())->toBe('orange');
            expect(Priority::MEDIUM->color())->toBe('yellow');
            expect(Priority::LOW->color())->toBe('gray');
        });
    });

    describe('weight', function () {
        it('returns correct weights', function () {
            expect(Priority::CRITICAL->weight())->toBe(4);
            expect(Priority::HIGH->weight())->toBe(3);
            expect(Priority::MEDIUM->weight())->toBe(2);
            expect(Priority::LOW->weight())->toBe(1);
        });

        it('has weights in descending order', function () {
            expect(Priority::CRITICAL->weight())->toBeGreaterThan(Priority::HIGH->weight());
            expect(Priority::HIGH->weight())->toBeGreaterThan(Priority::MEDIUM->weight());
            expect(Priority::MEDIUM->weight())->toBeGreaterThan(Priority::LOW->weight());
        });
    });

    describe('comparison methods', function () {
        describe('isHigherThan', function () {
            it('correctly compares priorities', function () {
                expect(Priority::CRITICAL->isHigherThan(Priority::HIGH))->toBeTrue();
                expect(Priority::CRITICAL->isHigherThan(Priority::MEDIUM))->toBeTrue();
                expect(Priority::CRITICAL->isHigherThan(Priority::LOW))->toBeTrue();

                expect(Priority::HIGH->isHigherThan(Priority::MEDIUM))->toBeTrue();
                expect(Priority::HIGH->isHigherThan(Priority::LOW))->toBeTrue();

                expect(Priority::MEDIUM->isHigherThan(Priority::LOW))->toBeTrue();
            });

            it('returns false when comparing equal priorities', function () {
                expect(Priority::CRITICAL->isHigherThan(Priority::CRITICAL))->toBeFalse();
                expect(Priority::HIGH->isHigherThan(Priority::HIGH))->toBeFalse();
                expect(Priority::MEDIUM->isHigherThan(Priority::MEDIUM))->toBeFalse();
                expect(Priority::LOW->isHigherThan(Priority::LOW))->toBeFalse();
            });

            it('returns false when comparing lower priorities', function () {
                expect(Priority::LOW->isHigherThan(Priority::MEDIUM))->toBeFalse();
                expect(Priority::LOW->isHigherThan(Priority::HIGH))->toBeFalse();
                expect(Priority::LOW->isHigherThan(Priority::CRITICAL))->toBeFalse();

                expect(Priority::MEDIUM->isHigherThan(Priority::HIGH))->toBeFalse();
                expect(Priority::MEDIUM->isHigherThan(Priority::CRITICAL))->toBeFalse();

                expect(Priority::HIGH->isHigherThan(Priority::CRITICAL))->toBeFalse();
            });
        });

        describe('isLowerThan', function () {
            it('correctly compares priorities', function () {
                expect(Priority::LOW->isLowerThan(Priority::MEDIUM))->toBeTrue();
                expect(Priority::LOW->isLowerThan(Priority::HIGH))->toBeTrue();
                expect(Priority::LOW->isLowerThan(Priority::CRITICAL))->toBeTrue();

                expect(Priority::MEDIUM->isLowerThan(Priority::HIGH))->toBeTrue();
                expect(Priority::MEDIUM->isLowerThan(Priority::CRITICAL))->toBeTrue();

                expect(Priority::HIGH->isLowerThan(Priority::CRITICAL))->toBeTrue();
            });

            it('returns false when comparing equal priorities', function () {
                expect(Priority::CRITICAL->isLowerThan(Priority::CRITICAL))->toBeFalse();
                expect(Priority::HIGH->isLowerThan(Priority::HIGH))->toBeFalse();
                expect(Priority::MEDIUM->isLowerThan(Priority::MEDIUM))->toBeFalse();
                expect(Priority::LOW->isLowerThan(Priority::LOW))->toBeFalse();
            });

            it('returns false when comparing higher priorities', function () {
                expect(Priority::CRITICAL->isLowerThan(Priority::HIGH))->toBeFalse();
                expect(Priority::CRITICAL->isLowerThan(Priority::MEDIUM))->toBeFalse();
                expect(Priority::CRITICAL->isLowerThan(Priority::LOW))->toBeFalse();

                expect(Priority::HIGH->isLowerThan(Priority::MEDIUM))->toBeFalse();
                expect(Priority::HIGH->isLowerThan(Priority::LOW))->toBeFalse();

                expect(Priority::MEDIUM->isLowerThan(Priority::LOW))->toBeFalse();
            });
        });

        describe('isEqualTo', function () {
            it('returns true for same priorities', function () {
                expect(Priority::CRITICAL->isEqualTo(Priority::CRITICAL))->toBeTrue();
                expect(Priority::HIGH->isEqualTo(Priority::HIGH))->toBeTrue();
                expect(Priority::MEDIUM->isEqualTo(Priority::MEDIUM))->toBeTrue();
                expect(Priority::LOW->isEqualTo(Priority::LOW))->toBeTrue();
            });

            it('returns false for different priorities', function () {
                expect(Priority::CRITICAL->isEqualTo(Priority::HIGH))->toBeFalse();
                expect(Priority::HIGH->isEqualTo(Priority::MEDIUM))->toBeFalse();
                expect(Priority::MEDIUM->isEqualTo(Priority::LOW))->toBeFalse();
                expect(Priority::LOW->isEqualTo(Priority::CRITICAL))->toBeFalse();
            });
        });

        describe('isAtLeast', function () {
            it('returns true for higher or equal priorities', function () {
                expect(Priority::CRITICAL->isAtLeast(Priority::CRITICAL))->toBeTrue();
                expect(Priority::CRITICAL->isAtLeast(Priority::HIGH))->toBeTrue();
                expect(Priority::CRITICAL->isAtLeast(Priority::MEDIUM))->toBeTrue();
                expect(Priority::CRITICAL->isAtLeast(Priority::LOW))->toBeTrue();

                expect(Priority::HIGH->isAtLeast(Priority::HIGH))->toBeTrue();
                expect(Priority::HIGH->isAtLeast(Priority::MEDIUM))->toBeTrue();
                expect(Priority::HIGH->isAtLeast(Priority::LOW))->toBeTrue();

                expect(Priority::MEDIUM->isAtLeast(Priority::MEDIUM))->toBeTrue();
                expect(Priority::MEDIUM->isAtLeast(Priority::LOW))->toBeTrue();

                expect(Priority::LOW->isAtLeast(Priority::LOW))->toBeTrue();
            });

            it('returns false for lower priorities', function () {
                expect(Priority::LOW->isAtLeast(Priority::MEDIUM))->toBeFalse();
                expect(Priority::LOW->isAtLeast(Priority::HIGH))->toBeFalse();
                expect(Priority::LOW->isAtLeast(Priority::CRITICAL))->toBeFalse();

                expect(Priority::MEDIUM->isAtLeast(Priority::HIGH))->toBeFalse();
                expect(Priority::MEDIUM->isAtLeast(Priority::CRITICAL))->toBeFalse();

                expect(Priority::HIGH->isAtLeast(Priority::CRITICAL))->toBeFalse();
            });
        });

        describe('isAtMost', function () {
            it('returns true for lower or equal priorities', function () {
                expect(Priority::LOW->isAtMost(Priority::LOW))->toBeTrue();
                expect(Priority::LOW->isAtMost(Priority::MEDIUM))->toBeTrue();
                expect(Priority::LOW->isAtMost(Priority::HIGH))->toBeTrue();
                expect(Priority::LOW->isAtMost(Priority::CRITICAL))->toBeTrue();

                expect(Priority::MEDIUM->isAtMost(Priority::MEDIUM))->toBeTrue();
                expect(Priority::MEDIUM->isAtMost(Priority::HIGH))->toBeTrue();
                expect(Priority::MEDIUM->isAtMost(Priority::CRITICAL))->toBeTrue();

                expect(Priority::HIGH->isAtMost(Priority::HIGH))->toBeTrue();
                expect(Priority::HIGH->isAtMost(Priority::CRITICAL))->toBeTrue();

                expect(Priority::CRITICAL->isAtMost(Priority::CRITICAL))->toBeTrue();
            });

            it('returns false for higher priorities', function () {
                expect(Priority::CRITICAL->isAtMost(Priority::HIGH))->toBeFalse();
                expect(Priority::CRITICAL->isAtMost(Priority::MEDIUM))->toBeFalse();
                expect(Priority::CRITICAL->isAtMost(Priority::LOW))->toBeFalse();

                expect(Priority::HIGH->isAtMost(Priority::MEDIUM))->toBeFalse();
                expect(Priority::HIGH->isAtMost(Priority::LOW))->toBeFalse();

                expect(Priority::MEDIUM->isAtMost(Priority::LOW))->toBeFalse();
            });
        });
    });

    describe('convenience methods', function () {
        describe('isCritical', function () {
            it('returns true only for CRITICAL priority', function () {
                expect(Priority::CRITICAL->isCritical())->toBeTrue();
                expect(Priority::HIGH->isCritical())->toBeFalse();
                expect(Priority::MEDIUM->isCritical())->toBeFalse();
                expect(Priority::LOW->isCritical())->toBeFalse();
            });
        });

        describe('isHighOrCritical', function () {
            it('returns true for HIGH and CRITICAL priorities', function () {
                expect(Priority::CRITICAL->isHighOrCritical())->toBeTrue();
                expect(Priority::HIGH->isHighOrCritical())->toBeTrue();
                expect(Priority::MEDIUM->isHighOrCritical())->toBeFalse();
                expect(Priority::LOW->isHighOrCritical())->toBeFalse();
            });
        });

        describe('isLow', function () {
            it('returns true only for LOW priority', function () {
                expect(Priority::LOW->isLow())->toBeTrue();
                expect(Priority::MEDIUM->isLow())->toBeFalse();
                expect(Priority::HIGH->isLow())->toBeFalse();
                expect(Priority::CRITICAL->isLow())->toBeFalse();
            });
        });
    });

    describe('static comparison methods', function () {
        describe('max', function () {
            it('returns the higher priority', function () {
                expect(Priority::max(Priority::CRITICAL, Priority::HIGH))->toBe(Priority::CRITICAL);
                expect(Priority::max(Priority::HIGH, Priority::CRITICAL))->toBe(Priority::CRITICAL);

                expect(Priority::max(Priority::HIGH, Priority::MEDIUM))->toBe(Priority::HIGH);
                expect(Priority::max(Priority::MEDIUM, Priority::HIGH))->toBe(Priority::HIGH);

                expect(Priority::max(Priority::MEDIUM, Priority::LOW))->toBe(Priority::MEDIUM);
                expect(Priority::max(Priority::LOW, Priority::MEDIUM))->toBe(Priority::MEDIUM);
            });

            it('returns the same priority when both are equal', function () {
                expect(Priority::max(Priority::CRITICAL, Priority::CRITICAL))->toBe(Priority::CRITICAL);
                expect(Priority::max(Priority::HIGH, Priority::HIGH))->toBe(Priority::HIGH);
                expect(Priority::max(Priority::MEDIUM, Priority::MEDIUM))->toBe(Priority::MEDIUM);
                expect(Priority::max(Priority::LOW, Priority::LOW))->toBe(Priority::LOW);
            });
        });

        describe('min', function () {
            it('returns the lower priority', function () {
                expect(Priority::min(Priority::CRITICAL, Priority::HIGH))->toBe(Priority::HIGH);
                expect(Priority::min(Priority::HIGH, Priority::CRITICAL))->toBe(Priority::HIGH);

                expect(Priority::min(Priority::HIGH, Priority::MEDIUM))->toBe(Priority::MEDIUM);
                expect(Priority::min(Priority::MEDIUM, Priority::HIGH))->toBe(Priority::MEDIUM);

                expect(Priority::min(Priority::MEDIUM, Priority::LOW))->toBe(Priority::LOW);
                expect(Priority::min(Priority::LOW, Priority::MEDIUM))->toBe(Priority::LOW);
            });

            it('returns the same priority when both are equal', function () {
                expect(Priority::min(Priority::CRITICAL, Priority::CRITICAL))->toBe(Priority::CRITICAL);
                expect(Priority::min(Priority::HIGH, Priority::HIGH))->toBe(Priority::HIGH);
                expect(Priority::min(Priority::MEDIUM, Priority::MEDIUM))->toBe(Priority::MEDIUM);
                expect(Priority::min(Priority::LOW, Priority::LOW))->toBe(Priority::LOW);
            });
        });
    });

    describe('static helper methods', function () {
        describe('ordered', function () {
            it('returns priorities in descending order', function () {
                $ordered = Priority::ordered();

                expect($ordered)->toHaveCount(4);
                expect($ordered[0])->toBe(Priority::CRITICAL);
                expect($ordered[1])->toBe(Priority::HIGH);
                expect($ordered[2])->toBe(Priority::MEDIUM);
                expect($ordered[3])->toBe(Priority::LOW);
            });
        });

        describe('all', function () {
            it('returns all priority cases', function () {
                $all = Priority::all();

                expect($all)->toHaveCount(4);
                expect($all)->toContain(Priority::CRITICAL);
                expect($all)->toContain(Priority::HIGH);
                expect($all)->toContain(Priority::MEDIUM);
                expect($all)->toContain(Priority::LOW);
            });
        });

        describe('values', function () {
            it('returns all priority string values', function () {
                $values = Priority::values();

                expect($values)->toHaveCount(4);
                expect($values)->toContain('critical');
                expect($values)->toContain('high');
                expect($values)->toContain('medium');
                expect($values)->toContain('low');
            });
        });
    });

    describe('BackedEnum methods', function () {
        it('can be created from string value', function () {
            expect(Priority::from('critical'))->toBe(Priority::CRITICAL);
            expect(Priority::from('high'))->toBe(Priority::HIGH);
            expect(Priority::from('medium'))->toBe(Priority::MEDIUM);
            expect(Priority::from('low'))->toBe(Priority::LOW);
        });

        it('throws exception for invalid value', function () {
            Priority::from('invalid');
        })->throws(ValueError::class);

        it('can try to create from string value', function () {
            expect(Priority::tryFrom('critical'))->toBe(Priority::CRITICAL);
            expect(Priority::tryFrom('high'))->toBe(Priority::HIGH);
            expect(Priority::tryFrom('medium'))->toBe(Priority::MEDIUM);
            expect(Priority::tryFrom('low'))->toBe(Priority::LOW);
            expect(Priority::tryFrom('invalid'))->toBeNull();
        });
    });

    describe('sorting behavior', function () {
        it('can sort priorities by weight', function () {
            $priorities = [
                Priority::LOW,
                Priority::CRITICAL,
                Priority::MEDIUM,
                Priority::HIGH,
            ];

            usort($priorities, fn ($a, $b) => $b->weight() <=> $a->weight());

            expect($priorities[0])->toBe(Priority::CRITICAL);
            expect($priorities[1])->toBe(Priority::HIGH);
            expect($priorities[2])->toBe(Priority::MEDIUM);
            expect($priorities[3])->toBe(Priority::LOW);
        });
    });
});
