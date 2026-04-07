<?php

use App\Models\AgendaExport;
use App\Models\AgendaReservation;
use App\Models\Career;
use App\Services\AgendaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reserves items', function () {
    $career = Career::factory()->create();
    $service = app(AgendaService::class);

    $service->reserveItems($career, 100, 5, 'medium');

    expect(AgendaReservation::count())->toBe(1);
    $res = AgendaReservation::first();
    expect($res->career_id)->toBe($career->id)
        ->and($res->reserved_coins)->toBe(100)
        ->and($res->reserved_hammers)->toBe(5)
        ->and($res->target_ts_distance)->toBe('medium');
});

it('exports agenda', function () {
    $career = Career::factory()->create();
    $service = app(AgendaService::class);

    \Storage::fake('local');

    $service->exportAgenda($career, 'header\nrow');

    expect(AgendaExport::count())->toBe(1);
    $export = AgendaExport::first();
    expect($export->career_id)->toBe($career->id)
        ->and($export->version)->toBe(1);

    \Storage::disk('local')->assertExists($export->file_path);
});
