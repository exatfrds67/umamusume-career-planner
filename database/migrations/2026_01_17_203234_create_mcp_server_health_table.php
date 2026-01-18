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
        Schema::create('ucp_mcp_server_health', function (Blueprint $table) {
            $table->id();
            $table->string('server_name', 100)->index(); // strands-agents, agentcore-mcp-server, etc.
            $table->string('status', 20)->index(); // healthy, unhealthy, disabled, unknown
            $table->boolean('is_connected')->default(false);
            $table->decimal('response_time', 10, 3)->nullable(); // seconds
            $table->integer('consecutive_failures')->default(0);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('capabilities')->nullable(); // server capabilities
            $table->json('metadata')->nullable(); // additional server info
            $table->timestamps();

            // Indexes for health monitoring
            $table->index(['server_name', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('created_at'); // for time-series queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_mcp_server_health');
    }
};
