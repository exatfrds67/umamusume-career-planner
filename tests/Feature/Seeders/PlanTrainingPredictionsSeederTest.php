<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\TrainingPrediction;
use App\Models\User;
use Database\Seeders\PlanTrainingPredictionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Sets up an admin user and 8 career stubs needed by the seeder.
 *
 * @return array{admin: User, careers: \Illuminate\Database\Eloquent\Collection<int, Career>}
 */
function seederFixtures(): array
{
    $admin = User::factory()->create(['email' => 'admin@umamusume.local']);
    // Create 8 careers in sequence so auto-increment assigns IDs 1–8 on the fresh test DB.
    $careers = Career::factory()->count(8)->create(['user_id' => $admin->id]);

    return ['admin' => $admin, 'careers' => $careers];
}

describe('PlanTrainingPredictionsSeeder', function () {
    it('creates 24 training prediction records — 3 per career for 8 careers', function () {
        seederFixtures();

        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);

        expect(TrainingPrediction::count())->toBe(24);
    });

    it('creates exactly 3 predictions of distinct types per career', function () {
        seederFixtures();

        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);

        $expectedTypes = collect(['stat_projection', 'race_outcome', 'sp_efficiency'])->sort()->values()->toArray();

        for ($careerId = 1; $careerId <= 8; $careerId++) {
            $types = TrainingPrediction::where('career_id', $careerId)
                ->pluck('prediction_type')
                ->sort()
                ->values()
                ->toArray();

            expect($types)->toBe($expectedTypes);
        }
    });

    it('stores actual_value and accuracy_score for completed careers (2, 3, 4)', function () {
        seederFixtures();

        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);

        foreach ([2, 3, 4] as $careerId) {
            TrainingPrediction::where('career_id', $careerId)->get()->each(function (TrainingPrediction $p) use ($careerId) {
                expect($p->actual_value)->not->toBeNull("career {$careerId} / {$p->prediction_type} should have actual_value");
                expect($p->accuracy_score)->not->toBeNull("career {$careerId} / {$p->prediction_type} should have accuracy_score");
            });
        }
    });

    it('leaves actual_value and accuracy_score null for planning careers (1, 5, 6, 7, 8)', function () {
        seederFixtures();

        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);

        foreach ([1, 5, 6, 7, 8] as $careerId) {
            TrainingPrediction::where('career_id', $careerId)->get()->each(function (TrainingPrediction $p) use ($careerId) {
                expect($p->actual_value)->toBeNull("career {$careerId} / {$p->prediction_type} should have null actual_value");
                expect($p->accuracy_score)->toBeNull("career {$careerId} / {$p->prediction_type} should have null accuracy_score");
            });
        }
    });

    it('uses model_version v1.0-import for all records', function () {
        seederFixtures();

        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);

        expect(TrainingPrediction::where('model_version', '!=', 'v1.0-import')->count())->toBe(0);
    });

    it('is idempotent — skips seeding when predictions already exist for careers 1–8', function () {
        seederFixtures();

        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);
        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);

        expect(TrainingPrediction::count())->toBe(24);
    });

    it('stores a non-empty predicted_value JSON object on every record', function () {
        seederFixtures();

        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);

        TrainingPrediction::all()->each(fn (TrainingPrediction $p) => expect($p->predicted_value)->toBeArray()->not->toBeEmpty());
    });

    it('assigns all records to the admin user', function () {
        ['admin' => $admin] = seederFixtures();

        $this->artisan('db:seed', ['--class' => PlanTrainingPredictionsSeeder::class]);

        expect(TrainingPrediction::where('user_id', '!=', $admin->id)->count())->toBe(0);
    });
});
