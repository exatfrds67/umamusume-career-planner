<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RivalRaceOutcome;
use App\Enums\TsDistance;
use App\Models\Career;
use App\Models\RivalLog;
use Illuminate\Support\Collection;

class TrackblazerService
{
    /**
     * Log a rival encounter in a race.
     *
     * @param  array<int, string>  $skillHints
     */
    public function logRivalEncounter(
        Career $career,
        int $raceId,
        TsDistance $distance,
        string $rivalUma,
        RivalRaceOutcome $outcome,
        array $skillHints = []
    ): RivalLog {
        return RivalLog::create([
            'career_id' => $career->id,
            'race_id' => $raceId,
            'distance' => $distance->value,
            'rival_uma' => $rivalUma,
            'outcome' => $outcome->value,
            'skill_hints' => $skillHints,
        ]);
    }

    /**
     * Get all rival logs for a career.
     *
     * @return Collection<int, RivalLog>
     */
    public function getRivalLogs(Career $career): Collection
    {
        return RivalLog::where('career_id', $career->id)->orderBy('created_at', 'desc')->get();
    }
}
