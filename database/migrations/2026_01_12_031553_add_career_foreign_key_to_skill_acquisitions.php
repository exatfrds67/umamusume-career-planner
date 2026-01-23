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
            $table->foreign('career_id')->references('id')->on('ucp_careers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_skill_acquisitions', function (Blueprint $table) {
            $table->dropForeign(['career_id']);
        });
    }
};
