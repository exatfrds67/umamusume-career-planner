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
        Schema::table('ucp_ocr_extractions', function (Blueprint $table) {
            // Drop the incorrect foreign key constraint
            $table->dropForeign(['user_id']);

            // Add the correct foreign key constraint pointing to ucp_users
            $table->foreign('user_id')
                ->references('id')
                ->on('ucp_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_ocr_extractions', function (Blueprint $table) {
            // Drop the corrected foreign key
            $table->dropForeign(['user_id']);

            // Restore the original (incorrect) foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
