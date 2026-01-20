<?php

declare(strict_types=1);

/**
 * Architecture Tests for Umamusume Career Planner
 *
 * These tests verify code structure and dependencies to ensure
 * code quality standards are maintained throughout the application.
 *
 * @see https://pestphp.com/docs/arch-testing
 */
arch('no debugging statements in production code')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('controllers should have controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('form requests should have request suffix')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request');

arch('enums should be enums')
    ->expect('App\Enums')
    ->toBeEnums();

arch('traits should be traits')
    ->expect('App\Traits')
    ->toBeTraits();

arch('interfaces should be interfaces')
    ->expect('App\Contracts')
    ->toBeInterfaces();

arch('agents should have agent suffix')
    ->expect('App\Services\Agents')
    ->toHaveSuffix('Agent');

arch('ai agents should have agent suffix')
    ->expect('App\Services\AI\Agents')
    ->toHaveSuffix('Agent')
    ->ignoring('App\Services\AI\Agents\AgentOrchestrationService');
