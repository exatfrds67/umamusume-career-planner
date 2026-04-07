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
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->json('synergy_snapshot')->nullable()->after('completion_data');
            $table->timestamp('synergy_computed_at')->nullable()->after('synergy_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->dropColumn(['synergy_snapshot', 'synergy_computed_at']);
        });
    }
};
