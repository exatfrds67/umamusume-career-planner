<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ucp_advisory_recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_id');
            $table->integer('turn_number');
            $table->string('recommendation_type', 50);
            $table->string('priority', 20);
            $table->text('action');
            $table->text('reasoning');
            $table->json('expected_outcomes');
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->boolean('was_followed')->default(false);
            $table->timestamp('created_at')->useCurrent();

            // Foreign key constraint
            $table->foreign('career_id')
                ->references('id')
                ->on('ucp_careers')
                ->onDelete('cascade');

            // Indexes
            $table->index(['career_id', 'turn_number'], 'idx_career_turn');
            $table->index('recommendation_type', 'idx_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_advisory_recommendations');
    }
};
