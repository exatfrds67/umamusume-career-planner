<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('prediction_accuracy table exists with correct structure', function () {
    expect(Schema::hasTable('ucp_prediction_accuracy'))->toBeTrue();

    // Check all required columns exist
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'id'))->toBeTrue();
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'career_id'))->toBeTrue();
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'turn_number'))->toBeTrue();
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'prediction_type'))->toBeTrue();
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'predicted_value'))->toBeTrue();
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'actual_value'))->toBeTrue();
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'accuracy_score'))->toBeTrue();
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'model_version'))->toBeTrue();
    expect(Schema::hasColumn('ucp_prediction_accuracy', 'created_at'))->toBeTrue();
});

test('prediction_accuracy table can store and retrieve data', function () {
    // Create a test user and character first
    $user = \App\Models\User::factory()->create();
    $character = \App\Models\Character::factory()->create();

    // Create a test career
    $career = DB::table('ucp_careers')->insertGetId([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'career_name' => 'Test Career',
        'scenario_type' => 'ura_finale',
        'status' => 'active',
        'current_turn' => 1,
        'current_phase' => 'junior',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert test data
    $predictionId = DB::table('ucp_prediction_accuracy')->insertGetId([
        'career_id' => $career,
        'turn_number' => 15,
        'prediction_type' => 'training_facility',
        'predicted_value' => json_encode(['speed_gain' => 50]),
        'actual_value' => json_encode(['speed_gain' => 48]),
        'accuracy_score' => 0.9600,
        'model_version' => 'v1.0.0',
        'created_at' => now(),
    ]);

    // Retrieve and verify
    $prediction = DB::table('ucp_prediction_accuracy')->find($predictionId);

    expect($prediction)->not->toBeNull();
    expect($prediction->career_id)->toBe($career);
    expect($prediction->turn_number)->toBe(15);
    expect($prediction->prediction_type)->toBe('training_facility');
    expect((float) $prediction->accuracy_score)->toBe(0.96);
    expect($prediction->model_version)->toBe('v1.0.0');

    // Verify JSON fields can be decoded
    $predictedValue = json_decode($prediction->predicted_value, true);
    $actualValue = json_decode($prediction->actual_value, true);
    expect($predictedValue)->toBeArray();
    expect($actualValue)->toBeArray();
    expect($predictedValue['speed_gain'])->toBe(50);
    expect($actualValue['speed_gain'])->toBe(48);
});

test('prediction_accuracy records are deleted when career is deleted', function () {
    // Create a test user and character
    $user = \App\Models\User::factory()->create();
    $character = \App\Models\Character::factory()->create();

    // Create a test career
    $career = DB::table('ucp_careers')->insertGetId([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'career_name' => 'Test Career',
        'scenario_type' => 'ura_finale',
        'status' => 'active',
        'current_turn' => 1,
        'current_phase' => 'junior',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert test prediction
    DB::table('ucp_prediction_accuracy')->insert([
        'career_id' => $career,
        'turn_number' => 15,
        'prediction_type' => 'training_facility',
        'predicted_value' => json_encode(['speed_gain' => 50]),
        'actual_value' => json_encode(['speed_gain' => 48]),
        'accuracy_score' => 0.9600,
        'model_version' => 'v1.0.0',
        'created_at' => now(),
    ]);

    // Verify prediction exists
    $count = DB::table('ucp_prediction_accuracy')->where('career_id', $career)->count();
    expect($count)->toBe(1);

    // Delete the career
    DB::table('ucp_careers')->where('id', $career)->delete();

    // Verify prediction was cascade deleted
    $count = DB::table('ucp_prediction_accuracy')->where('career_id', $career)->count();
    expect($count)->toBe(0);
});

test('prediction_accuracy table supports multiple prediction types', function () {
    // Create a test user and character
    $user = \App\Models\User::factory()->create();
    $character = \App\Models\Character::factory()->create();

    // Create a test career
    $career = DB::table('ucp_careers')->insertGetId([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'career_name' => 'Test Career',
        'scenario_type' => 'ura_finale',
        'status' => 'active',
        'current_turn' => 1,
        'current_phase' => 'junior',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert different prediction types
    $predictionTypes = [
        'training_facility',
        'skill_purchase',
        'race_strategy',
        'rest_recovery',
        'bond_building',
    ];

    foreach ($predictionTypes as $type) {
        DB::table('ucp_prediction_accuracy')->insert([
            'career_id' => $career,
            'turn_number' => 15,
            'prediction_type' => $type,
            'predicted_value' => json_encode(['test' => 'value']),
            'actual_value' => json_encode(['test' => 'value']),
            'accuracy_score' => 0.9500,
            'model_version' => 'v1.0.0',
            'created_at' => now(),
        ]);
    }

    // Verify all types were inserted
    $count = DB::table('ucp_prediction_accuracy')->where('career_id', $career)->count();
    expect($count)->toBe(count($predictionTypes));

    // Verify we can query by prediction type
    foreach ($predictionTypes as $type) {
        $prediction = DB::table('ucp_prediction_accuracy')
            ->where('career_id', $career)
            ->where('prediction_type', $type)
            ->first();

        expect($prediction)->not->toBeNull();
        expect($prediction->prediction_type)->toBe($type);
    }
});

test('prediction_accuracy table supports different model versions', function () {
    // Create a test user and character
    $user = \App\Models\User::factory()->create();
    $character = \App\Models\Character::factory()->create();

    // Create a test career
    $career = DB::table('ucp_careers')->insertGetId([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'career_name' => 'Test Career',
        'scenario_type' => 'ura_finale',
        'status' => 'active',
        'current_turn' => 1,
        'current_phase' => 'junior',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert predictions with different model versions
    $modelVersions = ['v1.0.0', 'v1.1.0', 'v2.0.0', 'ollama-llama3', 'bedrock-claude'];

    foreach ($modelVersions as $version) {
        DB::table('ucp_prediction_accuracy')->insert([
            'career_id' => $career,
            'turn_number' => 15,
            'prediction_type' => 'training_facility',
            'predicted_value' => json_encode(['test' => 'value']),
            'actual_value' => json_encode(['test' => 'value']),
            'accuracy_score' => 0.9500,
            'model_version' => $version,
            'created_at' => now(),
        ]);
    }

    // Verify we can query by model version
    foreach ($modelVersions as $version) {
        $prediction = DB::table('ucp_prediction_accuracy')
            ->where('career_id', $career)
            ->where('model_version', $version)
            ->first();

        expect($prediction)->not->toBeNull();
        expect($prediction->model_version)->toBe($version);
    }
});
