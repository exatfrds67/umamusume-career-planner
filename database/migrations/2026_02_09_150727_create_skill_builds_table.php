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
        Schema::create('ucp_skill_builds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('ucp_users')->cascadeOnDelete();
            $table->foreignId('character_id')->nullable()->constrained('ucp_characters')->nullOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('meta_tier')->nullable();
            $table->text('description')->nullable();
            $table->json('skill_ids');
            $table->integer('total_sp_cost')->default(0);
            $table->integer('optimized_cost')->default(0);
            $table->json('tags')->nullable();
            $table->boolean('is_template')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_template']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_skill_builds');
    }
};
