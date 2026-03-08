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
        Schema::create('ucp_parent_characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('ucp_careers')->cascadeOnDelete();
            $table->string('slot', 20); // 'parent_1', 'parent_2'
            $table->string('character_name');
            $table->string('scenario_type')->nullable();
            $table->string('running_style')->nullable();
            $table->string('preferred_distance')->nullable();
            $table->unsignedSmallInteger('final_speed')->default(0);
            $table->unsignedSmallInteger('final_stamina')->default(0);
            $table->unsignedSmallInteger('final_power')->default(0);
            $table->unsignedSmallInteger('final_guts')->default(0);
            $table->unsignedSmallInteger('final_wit')->default(0);
            $table->string('affinity_grade', 10)->default('standard'); // high, standard, low
            $table->json('skill_pool')->nullable();
            $table->json('factor_summary')->nullable();
            $table->json('parent_metadata')->nullable();
            $table->timestamps();

            $table->index(['career_id', 'slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_parent_characters');
    }
};
