<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AgendaExport;
use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaExport>
 */
class AgendaExportFactory extends Factory
{
    protected $model = AgendaExport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'file_path' => 'agendas/'.fake()->uuid().'.csv',
            'version' => fake()->numberBetween(1, 10),
        ];
    }
}
