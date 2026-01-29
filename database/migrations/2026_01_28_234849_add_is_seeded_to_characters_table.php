<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->boolean('is_seeded')->default(false)->after('is_pinned')->comment('Whether character is from seed data (accessible to all users)');
        });

        // Mark all existing characters as seeded
        DB::table('ucp_characters')->update(['is_seeded' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->dropColumn('is_seeded');
        });
    }
};
