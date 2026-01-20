<?php

declare(strict_types=1);

namespace App\Services\OCR;

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Data Integration Service
 *
 * Integrates extracted OCR data into the character management system.
 * Handles database updates and creates related records.
 *
 * Requirements: Task 5.1.4, Requirement 23.4
 */
class DataIntegrationService
{
    public function __construct(
        private readonly DataTransformationService $transformer
    ) {}

    /**
     * Import character stats into character record
     *
     * @param  array<string, mixed>  $extractedData
     * @return array{success: bool, character_id: int|null, updated_fields: array<string>, message: string}
     */
    public function importCharacterStats(int $characterId, array $extractedData): array
    {
        try {
            DB::beginTransaction();

            $character = Character::findOrFail($characterId);

            // Transform data
            $transformedData = $this->transformer->transformCharacterStats($extractedData);

            // Prepare update data
            $updateData = $this->transformer->prepareCharacterUpdate($transformedData);

            if (empty($updateData)) {
                DB::rollBack();

                return [
                    'success' => false,
                    'character_id' => $characterId,
                    'updated_fields' => [],
                    'message' => 'No valid data to update',
                ];
            }

            // Update character
            $character->update($updateData);

            DB::commit();

            Log::info('[DataIntegrationService] Character stats imported', [
                'character_id' => $characterId,
                'updated_fields' => \array_keys($updateData),
                'confidence' => $extractedData['confidence'] ?? 0.0,
            ]);

            return [
                'success' => true,
                'character_id' => $characterId,
                'updated_fields' => \array_keys($updateData),
                'message' => 'Character stats updated successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('[DataIntegrationService] Failed to import character stats', [
                'character_id' => $characterId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'character_id' => $characterId,
                'updated_fields' => [],
                'message' => 'Failed to import character stats: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Import training session data
     *
     * @param  array<string, mixed>  $extractedData
     * @return array{success: bool, training_session_id: int|null, message: string}
     */
    public function importTrainingSession(int $careerId, array $extractedData): array
    {
        try {
            DB::beginTransaction();

            $career = Career::findOrFail($careerId);

            // Transform data
            $transformedData = $this->transformer->transformTrainingSession($extractedData);

            // Prepare creation data
            $createData = $this->transformer->prepareTrainingSessionCreate($careerId, $transformedData);

            // Create training session
            $trainingSession = TrainingSession::create($createData);

            DB::commit();

            Log::info('[DataIntegrationService] Training session imported', [
                'career_id' => $careerId,
                'training_session_id' => $trainingSession->id,
                'training_type' => $transformedData['training_type'],
                'confidence' => $extractedData['confidence'] ?? 0.0,
            ]);

            return [
                'success' => true,
                'training_session_id' => $trainingSession->id,
                'message' => 'Training session created successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('[DataIntegrationService] Failed to import training session', [
                'career_id' => $careerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'training_session_id' => null,
                'message' => 'Failed to import training session: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Import race result data
     *
     * @param  array<string, mixed>  $extractedData
     * @return array{success: bool, race_id: int|null, message: string}
     */
    public function importRaceResult(int $careerId, array $extractedData): array
    {
        try {
            DB::beginTransaction();

            $career = Career::findOrFail($careerId);

            // Transform data
            $transformedData = $this->transformer->transformRaceResult($extractedData);

            // Prepare creation data
            $createData = $this->transformer->prepareRaceCreate($careerId, $transformedData);

            // Create race record
            $race = Race::create($createData);

            // Update character stats if available
            if (isset($extractedData['data']['fans_gained']) && $extractedData['data']['fans_gained'] > 0) {
                $character = $career->character;
                // Note: Assuming character has a fans_count field
                // $character->increment('fans_count', $extractedData['data']['fans_gained']);
            }

            DB::commit();

            Log::info('[DataIntegrationService] Race result imported', [
                'career_id' => $careerId,
                'race_id' => $race->id,
                'race_name' => $transformedData['race_name'],
                'position' => $transformedData['position'],
                'confidence' => $extractedData['confidence'] ?? 0.0,
            ]);

            return [
                'success' => true,
                'race_id' => $race->id,
                'message' => 'Race result created successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('[DataIntegrationService] Failed to import race result', [
                'career_id' => $careerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'race_id' => null,
                'message' => 'Failed to import race result: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Import skill list data
     *
     * @param  array<string, mixed>  $extractedData
     * @return array{success: bool, skills_processed: int, message: string}
     */
    public function importSkillList(int $characterId, array $extractedData): array
    {
        try {
            DB::beginTransaction();

            $character = Character::findOrFail($characterId);

            // Transform data
            $transformedData = $this->transformer->transformSkillList($extractedData);

            $skillsProcessed = 0;

            // Process each skill
            foreach ($transformedData['skills'] as $skillData) {
                if (empty($skillData['name'])) {
                    continue;
                }

                // Find or create skill
                $skill = Skill::firstOrCreate(
                    [
                        'character_id' => $characterId,
                        'skill_name' => $skillData['name'],
                    ],
                    [
                        'skill_type' => $skillData['skill_type'] ?? 'unknown',
                        'sp_cost' => $skillData['sp_cost'] ?? 0,
                        'hint_count' => $skillData['hint_level'] ?? 0,
                        'is_acquired' => $skillData['is_acquired'] ?? false,
                    ]
                );

                // Update if already exists
                if (! $skill->wasRecentlyCreated) {
                    $skill->update([
                        'sp_cost' => $skillData['sp_cost'] ?? $skill->sp_cost,
                        'hint_count' => $skillData['hint_level'] ?? $skill->hint_count,
                        'is_acquired' => $skillData['is_acquired'] ?? $skill->is_acquired,
                    ]);
                }

                $skillsProcessed++;
            }

            DB::commit();

            Log::info('[DataIntegrationService] Skill list imported', [
                'character_id' => $characterId,
                'skills_processed' => $skillsProcessed,
                'total_sp' => $transformedData['total_sp'],
                'confidence' => $extractedData['confidence'] ?? 0.0,
            ]);

            return [
                'success' => true,
                'skills_processed' => $skillsProcessed,
                'message' => "Successfully processed {$skillsProcessed} skills",
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('[DataIntegrationService] Failed to import skill list', [
                'character_id' => $characterId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'skills_processed' => 0,
                'message' => 'Failed to import skill list: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Batch import multiple screenshots
     *
     * @param  array<int, array{screen_type: string, extracted_data: array<string, mixed>, target_id: int}>  $imports
     * @return array{success: bool, processed: int, failed: int, results: array<int, array<string, mixed>>}
     */
    public function batchImport(array $imports): array
    {
        $results = [];
        $processed = 0;
        $failed = 0;

        foreach ($imports as $index => $import) {
            $result = match ($import['screen_type']) {
                'character_stats' => $this->importCharacterStats($import['target_id'], $import['extracted_data']),
                'training_session' => $this->importTrainingSession($import['target_id'], $import['extracted_data']),
                'race_result' => $this->importRaceResult($import['target_id'], $import['extracted_data']),
                'skill_list' => $this->importSkillList($import['target_id'], $import['extracted_data']),
                default => [
                    'success' => false,
                    'message' => "Unknown screen type: {$import['screen_type']}",
                ],
            };

            $results[$index] = $result;

            if ($result['success']) {
                $processed++;
            } else {
                $failed++;
            }
        }

        Log::info('[DataIntegrationService] Batch import completed', [
            'total' => \count($imports),
            'processed' => $processed,
            'failed' => $failed,
        ]);

        return [
            'success' => $failed === 0,
            'processed' => $processed,
            'failed' => $failed,
            'results' => $results,
        ];
    }
}
