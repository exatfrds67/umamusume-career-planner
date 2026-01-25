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
        Schema::create('support_decks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');
            $table->string('name')->default('Main Deck');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('character_id');
            $table->index(['character_id', 'is_active']);
        });

        Schema::create('support_deck_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_deck_id')->constrained('support_decks')->onDelete('cascade');
            $table->foreignId('support_card_id')->constrained('ucp_support_cards')->onDelete('cascade');
            $table->tinyInteger('position')->unsigned()->comment('Position in deck (1-6)');
            $table->tinyInteger('bond_level')->unsigned()->default(0)->comment('Bond level (0-100)');
            $table->boolean('is_borrowed')->default(false)->comment('Borrowed from friend');
            $table->timestamps();

            $table->unique(['support_deck_id', 'position'], 'unique_deck_position');
            $table->index('support_deck_id');
            $table->index('support_card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_deck_cards');
        Schema::dropIfExists('support_decks');
    }
};
