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
            $table->boolean('is_equipped')->default(false)->comment('Whether the skill is currently equipped')->after('is_active');
            $table->index('is_equipped');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_skill_acquisitions', function (Blueprint $table) {
            $table->dropIndex(['is_equipped']);
            $table->dropColumn('is_equipped');
        });
    }
};
