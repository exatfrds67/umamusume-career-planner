<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Consolidated Skill Seeder
 *
 * This is the primary seeder for all skill data in the application.
 * It replaces the legacy ComprehensiveSkillSeeder and RealUmaMusumeSkillsSeeder.
 *
 * Seeding Strategy:
 * 1. Loads curated skills from database/seeders/data/curated_skills.php
 * 2. Supplements with 500+ skills from gametora.com API
 * 3. Sets up evolution relationships between skills
 *
 * Curated metadata (meta_tier, strategic_notes, synergy_skills, description)
 * is preserved and will NOT be overwritten by external API data.
 *
 * Legacy seeders have been archived to database/seeders/deprecated/
 */
class UcpSkillsSeeder extends Seeder
{
    /**
     * Gametora skills JSON URL
     * Note: The gametora.com API endpoint is no longer publicly available.
     * The seeder will use curated skills from database/seeders/data/curated_skills.php
     *
     * If you have access to a gametora API endpoint, update this URL.
     */
    private const GAMETORA_SKILLS_URL = ''; // API no longer available (empty string to avoid null issues)

    /**
     * Whether to attempt fetching from gametora API
     */
    private const ENABLE_GAMETORA_FETCH = false;

    /**
     * Map gametora rarity values to our database rarity values
     */
    private const RARITY_MAP = [
        1 => 'normal',   // White/Normal skills
        2 => 'normal',   // Uncommon
        3 => 'rare',     // Gold/Rare skills
        4 => 'rare',     // Gold evolved
        5 => 'unique',   // Unique/Character-specific skills
    ];

    /**
     * Map skill type codes to readable types
     */
    private const TYPE_MAP = [
        'nac' => 'speed',        // Non-activation (passive speed)
        'str' => 'speed',        // Straight
        'cor' => 'speed',        // Corner
        'f_s' => 'speed',        // Final straight
        'f_c' => 'speed',        // Final corner
        'l_1' => 'recovery',     // Leg 1 (early race)
        'l_2' => 'speed',        // Leg 2 (mid race)
        'l_3' => 'speed',        // Leg 3 (late race)
        'lng' => 'speed',        // Long distance
        'mid' => 'speed',        // Middle distance
        'sht' => 'speed',        // Short distance
        'mle' => 'speed',        // Mile
        'drt' => 'speed',        // Dirt
        'trf' => 'speed',        // Turf
    ];

    /**
     * Counters for tracking seeding operations
     */
    private int $created = 0;

    private int $updated = 0;

    private int $skipped = 0;

    private int $errors = 0;

    /**
     * Whether to perform fresh seeding (truncate before seeding)
     */
    private bool $fresh = false;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Perform fresh seeding if requested
        if ($this->fresh) {
            if ($this->command) {
                $this->command->warn('Fresh seeding mode: truncating skills table...');
            }
            $this->truncateTable();
        }

        $existingCount = DB::table('ucp_skills')->count();
        if ($this->command) {
            $this->command->info("Current skills in database: {$existingCount}");
        }

        // Step 1: Seed curated skills first
        if ($this->command) {
            $this->command->info('Seeding curated skills...');
        }
        $this->seedCuratedSkills();

        // Step 2: Fetch and merge gametora skills (if enabled)
        if (self::ENABLE_GAMETORA_FETCH && ! empty(self::GAMETORA_SKILLS_URL)) {
            if ($this->command) {
                $this->command->info('Fetching additional skills from gametora.com...');
            }
            $this->fetchAndMergeGametoraSkills();
        } else {
            if ($this->command) {
                $this->command->info('Gametora API fetch disabled - using curated skills only');
            }
        }

        // Step 3: Setup evolution relationships
        if ($this->command) {
            $this->command->info('Setting up evolution relationships...');
        }
        $this->setupEvolutionRelationships();

        // Step 4: Report final results
        $this->reportResults();
    }

    /**
     * Enable fresh seeding mode (truncate before seeding)
     *
     * This method allows programmatic control of fresh seeding mode
     * without requiring command-line options.
     */
    public function fresh(): self
    {
        $this->fresh = true;

        return $this;
    }

    /**
     * Truncate the skills table with proper foreign key handling
     *
     * This method handles foreign key constraints for both MySQL and SQLite drivers.
     * For MySQL, it temporarily disables foreign key checks.
     * For SQLite, it uses PRAGMA to disable foreign keys during truncation.
     *
     * @throws \Exception If truncation fails
     */
    private function truncateTable(): void
    {
        $driver = DB::getDriverName();

        try {
            if ($driver === 'mysql') {
                // MySQL: Disable foreign key checks, truncate, re-enable
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('ucp_skills')->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                if ($this->command) {
                    $this->command->info('Table truncated (MySQL)');
                }
            } elseif ($driver === 'sqlite') {
                // SQLite: Use PRAGMA to disable foreign keys, delete all rows, re-enable
                DB::statement('PRAGMA foreign_keys = OFF;');
                DB::table('ucp_skills')->delete();
                // Reset auto-increment counter for SQLite
                DB::statement('DELETE FROM sqlite_sequence WHERE name = ?', ['ucp_skills']);
                DB::statement('PRAGMA foreign_keys = ON;');

                if ($this->command) {
                    $this->command->info('Table truncated (SQLite)');
                }
            } else {
                // Fallback for other drivers (PostgreSQL, SQL Server, etc.)
                DB::table('ucp_skills')->delete();

                if ($this->command) {
                    $this->command->warn("Table cleared using delete (driver: {$driver})");
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to truncate skills table', [
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Load curated skills from data file
     */
    private function loadCuratedSkills(): array
    {
        $dataPath = database_path('seeders/data/curated_skills.php');

        if (! file_exists($dataPath)) {
            Log::error('Curated skills data file not found', ['path' => $dataPath]);
            if ($this->command) {
                $this->command->error('Curated skills data file not found: '.$dataPath);
            }

            return ['skills' => [], 'evolution_pairs' => []];
        }

        $data = require $dataPath;

        if (! is_array($data) || ! isset($data['skills']) || ! is_array($data['skills'])) {
            Log::error('Invalid curated skills data structure');
            if ($this->command) {
                $this->command->error('Invalid curated skills data structure');
            }

            return ['skills' => [], 'evolution_pairs' => []];
        }

        return $data;
    }

    /**
     * Get evolution pairs from curated skills data file
     *
     * @return array Array of evolution pairs [source_internal_id, target_internal_id]
     */
    private function getEvolutionPairs(): array
    {
        $data = $this->loadCuratedSkills();

        return $data['evolution_pairs'] ?? [];
    }

    /**
     * Seed curated skills from data file
     */
    private function seedCuratedSkills(): void
    {
        $data = $this->loadCuratedSkills();
        $skills = $data['skills'] ?? [];

        if (empty($skills)) {
            if ($this->command) {
                $this->command->warn('No curated skills found to seed');
            }

            return;
        }

        if ($this->command) {
            $this->command->info('Found '.\count($skills).' curated skills');
        }

        $progressBar = $this->command ? $this->command->getOutput()->createProgressBar(\count($skills)) : null;
        $progressBar?->start();

        foreach ($skills as $skillData) {
            try {
                // Validate required fields
                $requiredFields = ['name', 'internal_id', 'skill_type', 'rarity', 'base_sp_cost'];
                $missingFields = [];

                foreach ($requiredFields as $field) {
                    if (! isset($skillData[$field]) || $skillData[$field] === '' || $skillData[$field] === null) {
                        $missingFields[] = $field;
                    }
                }

                if (! empty($missingFields)) {
                    Log::warning('Curated skill missing required fields', [
                        'skill' => $skillData['name'] ?? $skillData['internal_id'] ?? 'unknown',
                        'missing_fields' => $missingFields,
                    ]);
                    $this->errors++;
                    $progressBar->advance();

                    continue;
                }

                // Upsert the skill
                $result = $this->upsertSkill($skillData);

                if ($result === 'created') {
                    $this->created++;
                } elseif ($result === 'updated') {
                    $this->updated++;
                } else {
                    $this->skipped++;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to process curated skill', [
                    'skill' => $skillData['name'] ?? $skillData['internal_id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                $this->errors++;
            }

            $progressBar?->advance();
        }

        $progressBar?->finish();
        if ($this->command) {
            $this->command->newLine(2);
            $this->command->info('Curated skills seeding complete');
        }
    }

    /**
     * Fetch and merge skills from gametora.com API
     *
     * This method fetches skills from the gametora API and merges them with existing skills.
     * Curated metadata fields (meta_tier, strategic_notes, synergy_skills, description) are
     * preserved and will NOT be overwritten by gametora data.
     */
    private function fetchAndMergeGametoraSkills(): void
    {
        // Check if URL is configured
        if (empty(self::GAMETORA_SKILLS_URL)) {
            if ($this->command) {
                $this->command->warn('Gametora API URL not configured');
            }

            return;
        }

        try {
            $response = Http::timeout(30)->get(self::GAMETORA_SKILLS_URL);

            if (! $response->successful()) {
                if ($this->command) {
                    $this->command->error('Failed to fetch skills from gametora: HTTP '.$response->status());
                    $this->command->info('Continuing with curated skills only.');
                }

                return;
            }

            $skills = $response->json();

            if (! \is_array($skills)) {
                if ($this->command) {
                    $this->command->error('Invalid response format from gametora');
                }

                return;
            }

            if ($this->command) {
                $this->command->info('Found '.\count($skills).' skills from gametora');
            }

            $progressBar = $this->command ? $this->command->getOutput()->createProgressBar(\count($skills)) : null;
            $progressBar?->start();

            foreach ($skills as $skillData) {
                try {
                    $result = $this->processSkill($skillData);
                    if ($result === 'created') {
                        $this->created++;
                    } elseif ($result === 'updated') {
                        $this->updated++;
                    } else {
                        $this->skipped++;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to process gametora skill', [
                        'skill_id' => $skillData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    $this->errors++;
                }

                $progressBar?->advance();
            }

            $progressBar?->finish();
            if ($this->command) {
                $this->command->newLine(2);
            }
        } catch (\Exception $e) {
            if ($this->command) {
                $this->command->error('Error fetching skills: '.$e->getMessage());
                $this->command->info('Continuing with curated skills only.');
            }
            Log::error('Failed to fetch gametora skills', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Report final seeding results
     */
    private function reportResults(): void
    {
        $finalCount = DB::table('ucp_skills')->count();

        if ($this->command) {
            $this->command->newLine();
            $this->command->info('=== Skills Seeding Complete ===');
            $this->command->info("  Created:     {$this->created}");
            $this->command->info("  Updated:     {$this->updated}");
            $this->command->info("  Skipped:     {$this->skipped}");
            $this->command->info("  Errors:      {$this->errors}");
            $this->command->info("  Total skills: {$finalCount}");
        }
    }

    /**
     * Upsert a skill into the database
     *
     * @param  array  $skillData  Skill data to insert or update
     * @param  bool  $preserveCuratedMetadata  If true, never overwrite curated metadata fields
     */
    private function upsertSkill(array $skillData, bool $preserveCuratedMetadata = false): string
    {
        $internalId = $skillData['internal_id'];

        // Define curated metadata fields that should be preserved
        $curatedMetadataFields = ['meta_tier', 'strategic_notes', 'synergy_skills', 'description'];

        // Check if skill exists by internal_id
        $existing = Skill::where('internal_id', $internalId)->first();

        if ($existing) {
            // Skill exists - update only null/empty fields
            $updated = false;

            foreach ($skillData as $key => $value) {
                if ($key === 'internal_id') {
                    continue; // Skip the unique key
                }

                // If preserving curated metadata, skip these fields entirely
                if ($preserveCuratedMetadata && \in_array($key, $curatedMetadataFields, true)) {
                    continue;
                }

                // Update if existing field is null or empty
                if (($existing->$key === null || $existing->$key === '') && $value !== null && $value !== '') {
                    $existing->$key = $value;
                    $updated = true;
                }
            }

            if ($updated) {
                $existing->save();

                return 'updated';
            }

            return 'skipped';
        }

        // Check if a skill with the same name already exists (different internal_id)
        // This prevents duplicate name constraint violations
        $duplicateName = Skill::where('name', $skillData['name'])->first();
        if ($duplicateName) {
            Log::info('Skipping skill - name already exists with different internal_id', [
                'new_internal_id' => $internalId,
                'existing_internal_id' => $duplicateName->internal_id,
                'name' => $skillData['name'],
            ]);

            return 'skipped';
        }

        // Create new skill
        Skill::create([
            'name' => $skillData['name'],
            'name_en' => $skillData['name_en'] ?? $skillData['name'],
            'internal_id' => $skillData['internal_id'],
            'skill_type' => $skillData['skill_type'],
            'rarity' => $skillData['rarity'],
            'base_sp_cost' => $skillData['base_sp_cost'],
            'description' => $skillData['description'] ?? '',
            'can_evolve' => $skillData['can_evolve'] ?? false,
            'is_evolution' => $skillData['is_evolution'] ?? false,
            'effects' => $skillData['effects'] ?? [],
            'activation_conditions' => $skillData['activation_conditions'] ?? null,
            'meta_tier' => $skillData['meta_tier'] ?? null,
            'strategic_notes' => $skillData['strategic_notes'] ?? null,
            'synergy_skills' => $skillData['synergy_skills'] ?? null,
            'is_active' => $skillData['is_active'] ?? true,
            'status' => $skillData['status'] ?? 'active',
        ]);

        return 'created';
    }

    /**
     * Process a single skill from gametora data
     */
    private function processSkill(array $data): string
    {
        // Skip skills without proper ID or name
        if (empty($data['id']) || (empty($data['jpname']) && empty($data['name_en']) && empty($data['enname']))) {
            return 'skipped';
        }

        $internalId = (string) $data['id'];

        // Determine the best English name
        $nameEn = $data['name_en'] ?? $data['enname'] ?? $data['jpname'] ?? 'Unknown Skill';

        // Skip skills with empty names after all fallbacks
        if (empty(trim($nameEn))) {
            Log::warning('Skipping skill with empty name', [
                'skill_id' => $internalId,
                'data' => $data,
            ]);

            return 'skipped';
        }

        // Determine description
        $description = $data['desc_en'] ?? $data['endesc'] ?? $data['jpdesc'] ?? '';

        // Determine rarity
        $rarityValue = $data['rarity'] ?? 1;
        $rarity = self::RARITY_MAP[$rarityValue] ?? 'normal';

        // Determine skill type from type array
        $skillType = $this->determineSkillType($data['type'] ?? []);

        // Get SP cost from gene_version (evolution data) or estimate
        $spCost = $data['gene_version']['cost'] ?? $this->estimateSpCost($rarity);

        // Determine if skill can evolve or is an evolution
        $canEvolve = ! empty($data['gene_version']);
        $isEvolution = $rarityValue >= 4 && ! empty($data['gene_version']['parent_skills']);

        // Determine meta tier based on rarity and effects
        // Note: This will be preserved if curated metadata exists
        $metaTier = $this->determineMetaTier($data);

        // Build effects JSON
        $effects = $this->buildEffects($data);

        // Prepare skill data for upsert
        // Include curated metadata fields (meta_tier, description) but they will be
        // preserved if they already exist in the database (via preserveCuratedMetadata flag)
        $skillData = [
            'name' => $nameEn,
            'name_en' => $nameEn,
            'internal_id' => $internalId,
            'skill_type' => $skillType,
            'rarity' => $rarity,
            'base_sp_cost' => $spCost,
            'description' => $description,
            'can_evolve' => $canEvolve,
            'is_evolution' => $isEvolution,
            'effects' => $effects,
            'meta_tier' => $metaTier,
            'is_active' => true,
            'status' => 'active',
        ];

        // Use preserveCuratedMetadata flag to ensure gametora data doesn't overwrite
        // curated metadata fields (meta_tier, strategic_notes, synergy_skills, description)
        return $this->upsertSkill($skillData, preserveCuratedMetadata: true);
    }

    /**
     * Determine skill type from gametora type array
     */
    private function determineSkillType(array $types): string
    {
        if (empty($types)) {
            return 'speed';
        }

        // Check for recovery skills first
        if (\in_array('l_1', $types, true)) {
            return 'recovery';
        }

        // Check for debuff indicators
        foreach ($types as $type) {
            if (str_contains((string) $type, 'debuff')) {
                return 'debuff';
            }
        }

        // Map first recognized type
        foreach ($types as $type) {
            if (isset(self::TYPE_MAP[$type])) {
                return self::TYPE_MAP[$type];
            }
        }

        return 'speed';
    }

    /**
     * Estimate SP cost based on rarity
     */
    private function estimateSpCost(string $rarity): int
    {
        return match ($rarity) {
            'unique' => 200,
            'rare' => 180,
            default => 150,
        };
    }

    /**
     * Determine meta tier based on skill data
     */
    private function determineMetaTier(array $data): ?string
    {
        $rarity = $data['rarity'] ?? 1;

        // Unique skills are generally high tier
        if ($rarity === 5) {
            return 'S';
        }

        // Gold/rare skills
        if ($rarity >= 3) {
            return 'A';
        }

        // Normal skills
        return 'B';
    }

    /**
     * Build effects JSON from gametora data
     */
    private function buildEffects(array $data): array
    {
        $effects = [];

        if (! empty($data['condition_groups'])) {
            foreach ($data['condition_groups'] as $group) {
                if (! empty($group['effects'])) {
                    foreach ($group['effects'] as $effect) {
                        $effects[] = [
                            'type' => $effect['type'] ?? 0,
                            'value' => $effect['value'] ?? 0,
                            'duration' => $group['base_time'] ?? 0,
                        ];
                    }
                }
            }
        }

        return $effects;
    }

    /**
     * Setup evolution relationships between skills
     *
     * This method processes evolution pairs from the curated skills data file
     * and updates the evolution_target_id and evolution_source_id foreign key fields.
     * Logs warnings for any evolution pairs that reference non-existent skills.
     */
    protected function setupEvolutionRelationships(): void
    {
        $evolutionPairs = $this->getEvolutionPairs();

        if (empty($evolutionPairs)) {
            if ($this->command) {
                $this->command->warn('No evolution pairs found to setup');
            }

            return;
        }

        if ($this->command) {
            $this->command->info('Found '.\count($evolutionPairs).' evolution pairs');
        }

        $progressBar = $this->command ? $this->command->getOutput()->createProgressBar(\count($evolutionPairs)) : null;
        $progressBar?->start();

        $setupCount = 0;
        $warningCount = 0;

        foreach ($evolutionPairs as $pair) {
            if (! \is_array($pair) || \count($pair) !== 2) {
                Log::warning('Invalid evolution pair format', ['pair' => $pair]);
                $warningCount++;
                $progressBar?->advance();

                continue;
            }

            [$sourceInternalId, $targetInternalId] = $pair;

            // Find source skill (normal skill that can evolve)
            $sourceSkill = Skill::where('internal_id', $sourceInternalId)->first();

            if (! $sourceSkill) {
                Log::warning('Evolution source skill not found', [
                    'internal_id' => $sourceInternalId,
                    'pair' => $pair,
                ]);
                $warningCount++;
                $progressBar?->advance();

                continue;
            }

            // Find target skill (rare/evolved skill)
            $targetSkill = Skill::where('internal_id', $targetInternalId)->first();

            if (! $targetSkill) {
                Log::warning('Evolution target skill not found', [
                    'internal_id' => $targetInternalId,
                    'pair' => $pair,
                ]);
                $warningCount++;
                $progressBar?->advance();

                continue;
            }

            // Update evolution relationships
            try {
                // Update source skill to point to target
                DB::table('ucp_skills')
                    ->where('id', $sourceSkill->id)
                    ->update(['evolution_target_id' => $targetSkill->id]);

                // Update target skill to point back to source
                DB::table('ucp_skills')
                    ->where('id', $targetSkill->id)
                    ->update(['evolution_source_id' => $sourceSkill->id]);

                $setupCount++;
            } catch (\Exception $e) {
                Log::error('Failed to setup evolution relationship', [
                    'source_id' => $sourceInternalId,
                    'target_id' => $targetInternalId,
                    'error' => $e->getMessage(),
                ]);
                $warningCount++;
            }

            $progressBar?->advance();
        }

        $progressBar?->finish();

        if ($this->command) {
            $this->command->newLine(2);
            $this->command->info("Evolution relationships setup: {$setupCount}");

            if ($warningCount > 0) {
                $this->command->warn("Warnings encountered: {$warningCount}");
            }
        }
    }
}
