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
        Schema::table('ucp_skill_acquisitions', function (Blueprint $table) {
            if (! Schema::hasColumn('ucp_skill_acquisitions', 'hint_level')) {
                $table->tinyInteger('hint_level')->unsigned()->default(0)->after('hint_sources')->comment('Number of hints obtained (0-2)');
            }
            if (! Schema::hasColumn('ucp_skill_acquisitions', 'sp_discount_applied')) {
                $table->tinyInteger('sp_discount_applied')->unsigned()->default(0)->after('hint_level')->comment('SP discount percentage from hints (0-40)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_skill_acquisitions', function (Blueprint $table) {
            if (Schema::hasColumn('ucp_skill_acquisitions', 'hint_level')) {
                $table->dropColumn('hint_level');
            }
            if (Schema::hasColumn('ucp_skill_acquisitions', 'sp_discount_applied')) {
                $table->dropColumn('sp_discount_applied');
            }
        });
    }
};
