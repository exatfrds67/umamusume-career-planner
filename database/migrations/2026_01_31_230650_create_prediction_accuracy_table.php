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
        Schema::create('ucp_prediction_accuracy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_id');
            $table->integer('turn_number');
            $table->string('prediction_type', 50);
            $table->json('predicted_value');
            $table->json('actual_value');
            $table->decimal('accuracy_score', 5, 4);
            $table->string('model_version', 50);
            $table->timestamp('created_at')->useCurrent();

            // Foreign key constraint
            $table->foreign('career_id')
                ->references('id')
                ->on('ucp_careers')
                ->onDelete('cascade');

            // Indexes
            $table->index('career_id', 'idx_career');
            $table->index(['prediction_type', 'model_version'], 'idx_type_model');
            $table->index('created_at', 'idx_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_prediction_accuracy');
    }
};
