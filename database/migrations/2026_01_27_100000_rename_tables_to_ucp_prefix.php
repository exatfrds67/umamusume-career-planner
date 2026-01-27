<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename support decks tables to match ucp_ prefix convention
        if (Schema::hasTable('support_decks')) {
            Schema::rename('support_decks', 'ucp_support_decks');
        }

        if (Schema::hasTable('support_deck_cards')) {
            Schema::rename('support_deck_cards', 'ucp_support_deck_cards');
        }

        // Rename OCR extracted skills table to match ucp_ prefix convention
        if (Schema::hasTable('ocr_extracted_skills')) {
            Schema::rename('ocr_extracted_skills', 'ucp_ocr_extracted_skills');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert table renames
        if (Schema::hasTable('ucp_support_decks')) {
            Schema::rename('ucp_support_decks', 'support_decks');
        }

        if (Schema::hasTable('ucp_support_deck_cards')) {
            Schema::rename('ucp_support_deck_cards', 'support_deck_cards');
        }

        if (Schema::hasTable('ucp_ocr_extracted_skills')) {
            Schema::rename('ucp_ocr_extracted_skills', 'ocr_extracted_skills');
        }
    }
};
