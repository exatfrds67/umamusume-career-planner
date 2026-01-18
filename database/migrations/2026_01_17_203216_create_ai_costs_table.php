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
        Schema::create('ucp_ai_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('ucp_users')->onDelete('cascade');
            $table->foreignId('character_id')->nullable()->constrained('ucp_characters')->onDelete('cascade');
            $table->string('provider', 50)->index(); // ollama, bedrock, mcp-strands, mcp-agentcore
            $table->string('model', 100)->index(); // model identifier
            $table->string('request_type', 50)->nullable(); // training, race, skill, general
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->decimal('input_cost', 10, 6)->default(0); // USD
            $table->decimal('output_cost', 10, 6)->default(0); // USD
            $table->decimal('total_cost', 10, 6)->default(0); // USD
            $table->decimal('response_time', 10, 3)->nullable(); // seconds
            $table->boolean('cached')->default(false);
            $table->text('request_summary')->nullable(); // brief description of request
            $table->timestamps();

            // Indexes for cost tracking and analysis
            $table->index(['user_id', 'created_at']);
            $table->index(['provider', 'created_at']);
            $table->index(['model', 'created_at']);
            $table->index(['request_type', 'created_at']);
            $table->index('created_at'); // for time-based queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_ai_costs');
    }
};
