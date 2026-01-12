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
        Schema::create('ucp_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->json('preferences')->nullable()->comment('User preferences and settings');
            $table->json('accessibility_settings')->nullable()->comment('WCAG 2.2 AA accessibility preferences');
            $table->json('ai_settings')->nullable()->comment('AI model preferences and budget limits');
            $table->json('mcp_settings')->nullable()->comment('MCP server preferences and configurations');
            $table->rememberToken();
            $table->timestamps();

            // Indexes for performance
            $table->index('uuid');
            $table->index('email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_users');
    }
};
