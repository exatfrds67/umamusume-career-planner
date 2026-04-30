<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ucp_achievements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('ucp_users')->cascadeOnDelete();
            $table->string('key', 80)->comment('Unique achievement identifier, e.g. first_race_win');
            $table->string('category', 40)->comment('Category: racing, training, skills, career, collection');
            $table->string('title', 120);
            $table->string('description', 255);
            $table->string('icon', 10)->default('🏆')->comment('Emoji icon');
            $table->string('rarity', 20)->default('common')->comment('common, rare, epic, legendary');
            $table->integer('progress')->default(0);
            $table->integer('target')->default(1);
            $table->boolean('is_unlocked')->default(false);
            $table->timestamp('unlocked_at')->nullable();
            $table->json('metadata')->nullable()->comment('Extra data: character_id, career_id, etc.');
            $table->timestamps();

            $table->unique(['user_id', 'key']);
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'is_unlocked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ucp_achievements');
    }
};
