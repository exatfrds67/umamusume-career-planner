<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AgendaExport;
use App\Models\AgendaReservation;
use App\Models\Career;

class AgendaService
{
    /**
     * Reserve coins, hammers, and distance items for an agenda.
     */
    public function reserveItems(Career $career, int $coins, int $hammers, ?string $targetDist = null): AgendaReservation
    {
        return AgendaReservation::updateOrCreate(
            ['career_id' => $career->id],
            [
                'reserved_coins' => $coins,
                'reserved_hammers' => $hammers,
                'target_ts_distance' => $targetDist,
            ]
        );
    }

    /**
     * Get agenda reservations.
     */
    public function getReservations(Career $career): ?AgendaReservation
    {
        return AgendaReservation::where('career_id', $career->id)->first();
    }

    /**
     * Export agenda to CSV string or similar, and log it.
     */
    public function exportAgenda(Career $career, string $csvData): AgendaExport
    {
        $version = AgendaExport::where('career_id', $career->id)->max('version');
        $newVersion = is_numeric($version) ? ((int) $version + 1) : 1;
        $fileName = sprintf('agenda_export_%s_v%d.csv', $career->id, $newVersion);

        // Pseudo logic: store to storage/app/agendas
        \Storage::disk('local')->put('agendas/'.$fileName, $csvData);

        return AgendaExport::create([
            'career_id' => $career->id,
            'file_path' => 'agendas/'.$fileName,
            'version' => $newVersion,
        ]);
    }
}
