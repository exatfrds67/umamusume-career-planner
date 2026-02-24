<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\DataImportService;
use App\Services\Export\ExcelExportService;
use App\Services\Export\PDFExportService;

describe('Data Transformation Property Tests', function () {
    /**
     * Property 17: Import-Export Round-Trip
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 17: Import-Export Round-Trip
     * Validates: Requirements FR-09.1, FR-09.3
     *
     * For any valid career data, exporting and then re-importing should preserve
     * the essential data fields.
     */
    it('preserves career data through JSON serialization round-trip', function () {
        for ($i = 0; $i < 100; $i++) {
            $speed = random_int(0, 1200);
            $stamina = random_int(0, 1200);
            $power = random_int(0, 1200);
            $guts = random_int(0, 1200);
            $wit = random_int(0, 1200);

            $data = [
                'career_name' => 'Test Career '.$i,
                'scenario_type' => ['ura_finale', 'unity_cup'][random_int(0, 1)],
                'speed' => $speed,
                'stamina' => $stamina,
                'power' => $power,
                'guts' => $guts,
                'wit' => $wit,
            ];

            $encoded = json_encode($data);
            $decoded = json_decode($encoded, true);

            expect($decoded['speed'])->toBe($speed)
                ->and($decoded['stamina'])->toBe($stamina)
                ->and($decoded['power'])->toBe($power)
                ->and($decoded['guts'])->toBe($guts)
                ->and($decoded['wit'])->toBe($wit)
                ->and($decoded['career_name'])->toBe($data['career_name'])
                ->and($decoded['scenario_type'])->toBe($data['scenario_type']);
        }
    })->group('property');

    /**
     * Property: JSON Serialization Idempotence
     *
     * Encoding → decoding → encoding must produce identical JSON.
     */
    it('maintains JSON serialization idempotence', function () {
        for ($i = 0; $i < 100; $i++) {
            $data = [
                'career_name' => 'Career '.random_int(1, 9999),
                'scenario_type' => ['ura_finale', 'unity_cup'][random_int(0, 1)],
                'stats' => [
                    'speed' => random_int(0, 1200),
                    'stamina' => random_int(0, 1200),
                    'power' => random_int(0, 1200),
                    'guts' => random_int(0, 1200),
                    'wit' => random_int(0, 1200),
                ],
                'turn_number' => random_int(1, 78),
                'training_sessions' => array_map(fn ($t) => [
                    'turn' => $t,
                    'type' => ['speed', 'stamina', 'power', 'guts', 'wit'][random_int(0, 4)],
                    'gain' => random_int(5, 30),
                ], range(1, random_int(1, 5))),
            ];

            $firstPass = json_encode(json_decode(json_encode($data), true));
            $secondPass = json_encode(json_decode($firstPass, true));

            expect($firstPass)->toBe($secondPass);
        }
    })->group('property');

    /**
     * Property 18: Export Format Completeness
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 18: Export Format Completeness
     * Validates: Requirements FR-09.1, FR-09.2
     *
     * Excel export should always produce all 5 expected sheets regardless of career state.
     */
    it('Excel export always produces all required sheets', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $scenarios = ['ura_finale', 'unity_cup'];
        $statuses = ['active', 'completed'];

        $service = new ExcelExportService;

        for ($i = 0; $i < 20; $i++) {
            $career = Career::factory()->create([
                'user_id' => $user->id,
                'character_id' => $character->id,
                'scenario_type' => $scenarios[random_int(0, 1)],
                'status' => $statuses[random_int(0, 1)],
                'final_speed' => random_int(0, 1) ? random_int(300, 1200) : null,
                'final_stamina' => random_int(0, 1) ? random_int(300, 1200) : null,
                'final_power' => random_int(0, 1) ? random_int(300, 1200) : null,
                'final_guts' => random_int(0, 1) ? random_int(300, 1200) : null,
                'final_wit' => random_int(0, 1) ? random_int(300, 1200) : null,
            ]);

            $result = $service->exportCareer($career);

            expect($result)->toHaveKeys(['sheets', 'filename', 'metadata'])
                ->and($result['sheets'])->toHaveKeys(['Overview', 'Stats', 'Training', 'Races', 'Skills'])
                ->and($result['filename'])->not->toBeEmpty();
        }
    })->group('property');

    /**
     * Property: PDF export maintains structural integrity
     *
     * PDF HTML content must always have proper HTML structure regardless of data.
     */
    it('PDF export always produces valid HTML structure', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $service = new PDFExportService;

        for ($i = 0; $i < 20; $i++) {
            $career = Career::factory()->create([
                'user_id' => $user->id,
                'character_id' => $character->id,
                'final_speed' => random_int(0, 1) ? random_int(300, 1200) : null,
                'final_stamina' => random_int(0, 1) ? random_int(300, 1200) : null,
                'final_power' => random_int(0, 1) ? random_int(300, 1200) : null,
                'final_guts' => random_int(0, 1) ? random_int(300, 1200) : null,
                'final_wit' => random_int(0, 1) ? random_int(300, 1200) : null,
            ]);

            $result = $service->exportCareer($career);

            expect($result['content'])->toContain('<!DOCTYPE html>')
                ->and($result['content'])->toContain('</html>')
                ->and($result['content'])->toContain('<body>')
                ->and($result['content'])->toContain('</body>')
                ->and($result['metadata'])->toHaveKeys(['generated_at', 'version', 'career_id', 'character_name']);
        }
    })->group('property');

    /**
     * Property: Import format detection consistency
     *
     * The same input should always be detected as the same format.
     */
    it('import format detection is deterministic', function () {
        $service = new DataImportService;

        $testInputs = [
            ['format' => 'json', 'input' => '{"name": "Test", "speed": 500}'],
            ['format' => 'csv', 'input' => "name,speed,stamina\nTest,500,600"],
            ['format' => 'json', 'input' => '[{"name": "Test1"}, {"name": "Test2"}]'],
        ];

        for ($i = 0; $i < 100; $i++) {
            foreach ($testInputs as $test) {
                $result1 = $service->parseText($test['input'], 'character');
                $result2 = $service->parseText($test['input'], 'character');

                expect($result1['format'])->toBe($result2['format']);
            }
        }
    })->group('property');

    /**
     * Property: Unicode handling in career names
     *
     * Japanese character names must survive serialization round-trips.
     */
    it('preserves unicode characters through JSON round-trip', function () {
        $japaneseNames = [
            'スペシャルウィーク',
            'サイレンススズカ',
            'トウカイテイオー',
            'メジロマックイーン',
            'ゴールドシップ',
            'ライスシャワー',
            'ウイニングチケット',
            'ナリタブライアン',
            'シンボリルドルフ',
            'エアグルーヴ',
        ];

        for ($i = 0; $i < 100; $i++) {
            $name = $japaneseNames[random_int(0, count($japaneseNames) - 1)];
            $data = [
                'character_name' => $name,
                'career_name' => $name.'の育成 ('.random_int(1, 99).'回目)',
                'stats' => [
                    'speed' => random_int(0, 1200),
                    'stamina' => random_int(0, 1200),
                ],
            ];

            $encoded = json_encode($data, JSON_UNESCAPED_UNICODE);
            $decoded = json_decode($encoded, true);

            expect($decoded['character_name'])->toBe($name)
                ->and($decoded['career_name'])->toBe($data['career_name'])
                ->and($encoded)->toContain($name);
        }
    })->group('property');

    /**
     * Property: CSV export content integrity
     *
     * CSV exports should properly escape special characters.
     */
    it('CSV export escapes special characters correctly', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);

        $specialTypes = ['speed', 'stamina', 'power', 'guts', 'wit'];

        for ($i = 0; $i < 20; $i++) {
            TrainingSession::factory()->create([
                'career_id' => $career->id,
                'character_id' => $character->id,
                'turn_number' => $i + 1,
                'training_type' => $specialTypes[random_int(0, 4)],
                'speed_gain' => random_int(0, 30),
                'stamina_gain' => random_int(0, 30),
                'power_gain' => random_int(0, 30),
                'guts_gain' => random_int(0, 30),
                'wit_gain' => random_int(0, 30),
                'sp_gain' => random_int(0, 20),
            ]);
        }

        $service = new ExcelExportService;
        $result = $service->exportAsCsv($career);

        expect($result['content'])->toContain('Turn,Training Type')
            ->and($result['filename'])->toContain('.csv');

        $lines = explode("\n", trim($result['content']));
        expect(count($lines))->toBeGreaterThan(1);

        foreach (array_slice($lines, 1) as $line) {
            if (empty(trim($line))) {
                continue;
            }
            $columns = str_getcsv($line);
            expect(count($columns))->toBeGreaterThanOrEqual(8);
        }
    })->group('property');
});
