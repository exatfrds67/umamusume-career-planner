<?php

declare(strict_types=1);

use App\Providers\MemoryGuardServiceProvider;

it('raises the memory limit when the current limit is lower', function () {
    expect(MemoryGuardServiceProvider::shouldApplyMemoryLimit('128M', '512M'))->toBeTrue();
});

it('does not lower the memory limit when the current limit is already higher', function () {
    expect(MemoryGuardServiceProvider::shouldApplyMemoryLimit('2G', '512M'))->toBeFalse();
});

it('does not override an unlimited memory limit', function () {
    expect(MemoryGuardServiceProvider::shouldApplyMemoryLimit('-1', '512M'))->toBeFalse();
});

it('parses memory limit strings into bytes', function () {
    expect(MemoryGuardServiceProvider::memoryLimitToBytes('1G'))->toBe(1073741824)
        ->and(MemoryGuardServiceProvider::memoryLimitToBytes('512M'))->toBe(536870912)
        ->and(MemoryGuardServiceProvider::memoryLimitToBytes('64K'))->toBe(65536)
        ->and(MemoryGuardServiceProvider::memoryLimitToBytes('-1'))->toBeNull();
});
