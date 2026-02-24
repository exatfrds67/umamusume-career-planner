<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ucp_training_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('ucp_careers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('ucp_users')->cascadeOnDelete();
            $table->unsignedInteger('turn_number');
            $table->string('prediction_type', 50);
            $table->json('predicted_value');
            $table->json('actual_value')->nullable();
            $table->decimal('confidence_score', 5, 4)->default(0);
            $table->decimal('accuracy_score', 5, 4)->nullable();
            $table->string('model_version', 50)->default('v1.0');
            $table->boolean('is_ab_test')->default(false);
            $table->string('ab_variant', 10)->nullable();
            $table->timestamps();

            $table->index(['career_id', 'turn_number']);
            $table->index(['prediction_type', 'model_version']);
            $table->index(['user_id', 'created_at']);
            $table->index('is_ab_test');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ucp_training_predictions');
    }
};
