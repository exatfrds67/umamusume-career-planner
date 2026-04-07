<?php

use App\Enums\RivalRaceOutcome;
use App\Enums\TsDistance;
use App\Models\Career;
use App\Models\RivalLog;
use App\Services\TrackblazerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a rival log successfully', function () {
    $career = Career::factory()->create();
    $service = app(TrackblazerService::class);

    $service->logRivalEncounter(
        $career,
        15,
        TsDistance::from('mile'),
        'Silence Suzuka',
        RivalRaceOutcome::from('won'),
        ['Escape Artist']
    );

    expect(RivalLog::count())->toBe(1);
    $log = RivalLog::first();
    expect($log->career_id)->toBe($career->id)
        ->and($log->distance)->toBe('mile')
        ->and($log->rival_uma)->toBe('Silence Suzuka')
        ->and($log->skill_hints)->toBeArray();
});

it('fetches rival logs for a career', function () {
    $career = Career::factory()->create();
    $service = app(TrackblazerService::class);

    $service->logRivalEncounter($career, 1, TsDistance::Sprint, 'El Condor Pasa', RivalRaceOutcome::Won);
    $service->logRivalEncounter($career, 2, TsDistance::Dirt, 'Smart Falcon', RivalRaceOutcome::Lost);

    $logs = $service->getRivalLogs($career);
    expect($logs)->toHaveCount(2);
});
