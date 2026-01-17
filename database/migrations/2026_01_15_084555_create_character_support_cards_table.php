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
        Schema::create('character_support_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');
            $table->foreignId('support_card_id')->constrained('ucp_support_cards')->onDelete('cascade');
            $table->integer('limit_break_level')->default(0)->comment('Current limit break level (0-4)');
            $table->integer('friendship_level')->default(0)->comment('Friendship level with this card');
            $table->integer('position_slot')->comment('Position slot (1-6)');
            $table->boolean('is_friend_card')->default(false)->comment('Whether this is the friend support card');
            $table->timestamps();

            // Unique constraint: one card per position per character
            $table->unique(['character_id', 'position_slot']);

            // Index for queries
            $table->index(['character_id', 'support_card_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_support_cards');
    }
};
