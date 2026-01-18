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
        Schema::create('ucp_ai_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->index(); // ollama, bedrock, mcp-strands, mcp-agentcore
            $table->string('model', 100)->index(); // llama3.3, claude-3-5-sonnet, etc.
            $table->integer('request_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->decimal('avg_response_time', 10, 3)->nullable(); // seconds
            $table->decimal('min_response_time', 10, 3)->nullable(); // seconds
            $table->decimal('max_response_time', 10, 3)->nullable(); // seconds
            $table->decimal('p95_response_time', 10, 3)->nullable(); // seconds
            $table->decimal('p99_response_time', 10, 3)->nullable(); // seconds
            $table->bigInteger('total_tokens')->default(0);
            $table->bigInteger('input_tokens')->default(0);
            $table->bigInteger('output_tokens')->default(0);
            $table->decimal('total_cost', 12, 6)->default(0); // USD
            $table->decimal('avg_confidence', 5, 3)->nullable(); // 0.000-1.000
            $table->string('period', 20)->index(); // hourly, daily, weekly, monthly
            $table->timestamp('period_start')->nullable()->index();
            $table->timestamp('period_end')->nullable()->index();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['provider', 'period_start']);
            $table->index(['model', 'period_start']);
            $table->index(['period', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_ai_metrics');
    }
};
