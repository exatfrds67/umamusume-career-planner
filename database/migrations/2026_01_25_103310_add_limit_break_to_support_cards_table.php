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
            if (! Schema::hasColumn('ucp_support_cards', 'limit_break')) {
                $table->tinyInteger('limit_break')->unsigned()->default(0)->after('rarity')->comment('Limit break level (0-4 stars)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_support_cards', function (Blueprint $table) {
            if (Schema::hasColumn('ucp_support_cards', 'limit_break')) {
                $table->dropColumn('limit_break');
            }
        });
    }
};
