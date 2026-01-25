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
        Schema::table('ucp_skills', function (Blueprint $table) {
            if (! Schema::hasColumn('ucp_skills', 'name_en')) {
                $table->string('name_en')->nullable()->after('name')->comment('English name of the skill');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_skills', function (Blueprint $table) {
            if (Schema::hasColumn('ucp_skills', 'name_en')) {
                $table->dropColumn('name_en');
            }
        });
    }
};
