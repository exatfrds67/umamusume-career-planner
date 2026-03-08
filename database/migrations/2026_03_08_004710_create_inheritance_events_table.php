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
        Schema::create('ucp_inheritance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('ucp_careers')->cascadeOnDelete();
            $table->foreignId('parent_character_id')->nullable()->constrained('ucp_parent_characters')->nullOnDelete();
            $table->unsignedTinyInteger('event_number'); // 1, 2, or 3
            $table->string('spark_type', 10); // blue, pink, green, white
            $table->unsignedTinyInteger('star_level'); // 1, 2, or 3
            $table->string('target_stat')->nullable(); // For blue/green sparks: speed, stamina, etc.
            $table->unsignedSmallInteger('stat_bonus')->nullable(); // Blue spark: flat stat points
            $table->decimal('growth_rate_bonus', 5, 2)->nullable(); // Green spark: growth rate %
            $table->unsignedSmallInteger('sp_bonus')->nullable(); // White spark: SP points
            $table->string('inherited_skill_name')->nullable(); // Pink spark: skill name
            $table->json('inherited_skill_data')->nullable(); // Pink spark: full skill data
            $table->json('choices_available')->nullable(); // Options player could choose from
            $table->json('choice_made')->nullable(); // What the player actually chose
            $table->boolean('is_applied')->default(false);
            $table->json('event_metadata')->nullable();
            $table->timestamps();

            $table->index(['career_id', 'event_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_inheritance_events');
    }
};
