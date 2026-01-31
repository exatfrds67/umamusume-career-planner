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
            $table->string('avatar_path')->nullable(); // Consolidated from 2026_01_17_173731
            $table->text('bio')->nullable()->comment('User biography/description'); // Consolidated from 2026_01_29_005544
            $table->string('password');
            $table->boolean('is_admin')->default(false); // Already exists, consolidated from 2026_01_29_084753
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
