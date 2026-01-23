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
        Schema::table('ucp_races', function (Blueprint $table) {
            $table->string('track_type')->nullable()->comment('Track direction/type (e.g., right, left, straight)')->after('surface');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_races', function (Blueprint $table) {
            $table->dropColumn('track_type');
        });
    }
};
