<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillHint;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

describe('Skill API Endpoints', function (): void {
    describe('GET /api/v1/skills', function (): void {
        it('returns list of skills', function (): void {
            Skill::factory()->count(10)->create(['is_active' => true]);

            $response = $this->getJson('/api/v1/skills');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'skill_type', 'base_sp_cost'],
                    ],
                ]);
        })->skip('API v1 skill routes not yet implemented');

        it('filters by skill type', function (): void {
            // Use valid skill_type enum values: speed, passive, recovery, debuff, unique
            Skill::factory()->count(5)->create([
                'skill_type' => 'speed',
                'is_active' => true,
            ]);
            Skill::factory()->count(3)->create([
                'skill_type' => 'passive',
                'is_active' => true,
            ]);

            $response = $this->getJson('/api/v1/skills?skill_type=speed');

            $response->assertSuccessful()
                ->assertJsonCount(5, 'data');
        })->skip('API v1 skill routes not yet implemented');

        it('searches by name', function (): void {
            Skill::factory()->create([
                'name' => 'Speed Boost',
                'is_active' => true,
            ]);
            Skill::factory()->create([
                'name' => 'Stamina Recovery',
                'is_active' => true,
            ]);

            $response = $this->getJson('/api/v1/skills?search=Speed');

            $response->assertSuccessful()
                ->assertJsonCount(1, 'data');
        })->skip('API v1 skill routes not yet implemented');

        it('filters by SP cost range', function (): void {
            Skill::factory()->count(3)->create([
                'base_sp_cost' => 100,
                'is_active' => true,
            ]);
            Skill::factory()->count(2)->create([
                'base_sp_cost' => 200,
                'is_active' => true,
            ]);

            $response = $this->getJson('/api/v1/skills?min_sp_cost=150');

            $response->assertSuccessful()
                ->assertJsonCount(2, 'data');
        })->skip('API v1 skill routes not yet implemented');
    });

    describe('GET /api/v1/skills/{id}', function (): void {
        it('returns skill details', function (): void {
            $skill = Skill::factory()->create();

            $response = $this->getJson("/api/v1/skills/{$skill->id}");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'skill_type',
                        'base_sp_cost',
                    ],
                ]);
        })->skip('API v1 skill routes not yet implemented');

        it('returns 404 for non-existent skill', function (): void {
            $response = $this->getJson('/api/v1/skills/99999');

            $response->assertNotFound();
        })->skip('API v1 skill routes not yet implemented');
    });

    describe('GET /api/v1/skills/{id}/hints', function (): void {
        it('returns skill hints', function (): void {
            $skill = Skill::factory()->create();
            SkillHint::factory()->count(3)->create(['skill_id' => $skill->id]);

            $response = $this->getJson("/api/v1/skills/{$skill->id}/hints");

            $response->assertSuccessful()
                ->assertJsonCount(3, 'data');
        })->skip('API v1 skill routes not yet implemented');
    });

    describe('Character Skill Acquisitions', function (): void {
        describe('GET /api/v1/characters/{id}/skills', function (): void {
            it('returns character acquired skills', function (): void {
                $character = Character::factory()->create(['user_id' => $this->user->id]);

                $skill1 = Skill::factory()->create();
                $skill2 = Skill::factory()->create();

                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill1->id,
                ]);
                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill2->id,
                ]);

                $response = $this->getJson("/api/v1/characters/{$character->id}/skills");

                $response->assertSuccessful()
                    ->assertJsonCount(2, 'data');
            })->skip('API v1 character skill routes not yet implemented');
        });

        describe('POST /api/v1/characters/{id}/skills', function (): void {
            it('acquires skill for character', function (): void {
                $character = Character::factory()->create([
                    'user_id' => $this->user->id,
                    'available_sp' => 500,
                ]);
                $skill = Skill::factory()->create(['base_sp_cost' => 100]);

                $response = $this->postJson("/api/v1/characters/{$character->id}/skills", [
                    'skill_id' => $skill->id,
                ]);

                $response->assertCreated();

                expect(SkillAcquisition::where([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ])->exists())->toBeTrue();
            })->skip('API v1 character skill routes not yet implemented');

            it('validates sufficient SP', function (): void {
                $character = Character::factory()->create([
                    'user_id' => $this->user->id,
                    'available_sp' => 50,
                ]);
                $skill = Skill::factory()->create(['base_sp_cost' => 100]);

                $response = $this->postJson("/api/v1/characters/{$character->id}/skills", [
                    'skill_id' => $skill->id,
                ]);

                $response->assertUnprocessable();
            })->skip('API v1 character skill routes not yet implemented');

            it('prevents duplicate skill acquisition', function (): void {
                $character = Character::factory()->create([
                    'user_id' => $this->user->id,
                    'available_sp' => 500,
                ]);
                $skill = Skill::factory()->create(['base_sp_cost' => 100]);

                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ]);

                $response = $this->postJson("/api/v1/characters/{$character->id}/skills", [
                    'skill_id' => $skill->id,
                ]);

                $response->assertUnprocessable();
            })->skip('API v1 character skill routes not yet implemented');
        });

        describe('DELETE /api/v1/characters/{id}/skills/{skillId}', function (): void {
            it('removes skill from character', function (): void {
                $character = Character::factory()->create(['user_id' => $this->user->id]);
                $skill = Skill::factory()->create();

                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ]);

                $response = $this->deleteJson("/api/v1/characters/{$character->id}/skills/{$skill->id}");

                $response->assertSuccessful();

                expect(SkillAcquisition::where([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ])->exists())->toBeFalse();
            })->skip('API v1 character skill routes not yet implemented');
        });
    });

    describe('Skill Analysis', function (): void {
        describe('GET /api/v1/skills/analysis/recommendations', function (): void {
            it('returns skill recommendations for character', function (): void {
                $character = Character::factory()->create(['user_id' => $this->user->id]);
                Skill::factory()->count(10)->create(['is_active' => true]);

                $response = $this->getJson("/api/v1/skills/analysis/recommendations?character_id={$character->id}");

                $response->assertSuccessful()
                    ->assertJsonStructure([
                        'data' => [
                            'recommended_skills',
                            'reasoning',
                        ],
                    ]);
            })->skip('API v1 skill analysis routes not yet implemented');
        });

        describe('GET /api/v1/skills/analysis/evolution', function (): void {
            it('returns skill evolution paths', function (): void {
                $skill = Skill::factory()->create();

                $response = $this->getJson("/api/v1/skills/analysis/evolution?skill_id={$skill->id}");

                $response->assertSuccessful()
                    ->assertJsonStructure([
                        'data' => [
                            'evolution_paths',
                            'prerequisites',
                        ],
                    ]);
            })->skip('API v1 skill analysis routes not yet implemented');
        });
    });
});
