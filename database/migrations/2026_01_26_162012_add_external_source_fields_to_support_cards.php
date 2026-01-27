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
        Schema::table('ucp_support_cards', function (Blueprint $table) {
            // External source tracking fields
            $table->string('external_source_id')->nullable()->after('internal_id');
            $table->string('external_source')->nullable()->after('external_source_id');
            $table->string('gametora_id')->nullable()->after('external_source');
            $table->unsignedInteger('chara_id')->nullable()->after('gametora_id');
            $table->enum('server_availability', ['jp', 'global', 'both'])->default('both')->after('is_active');

            // Indexes for efficient lookups
            $table->index('external_source_id');
            $table->index('gametora_id');
            $table->index('chara_id');
            $table->index('server_availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_support_cards', function (Blueprint $table) {
            $table->dropIndex(['external_source_id']);
            $table->dropIndex(['gametora_id']);
            $table->dropIndex(['chara_id']);
            $table->dropIndex(['server_availability']);

            $table->dropColumn([
                'external_source_id',
                'external_source',
                'gametora_id',
                'chara_id',
                'server_availability',
            ]);
        });
    }
};
