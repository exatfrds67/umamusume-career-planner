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
            $table->string('avatar_url', 500)->nullable()->after('name')->comment('Character avatar/image URL or path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->dropColumn('avatar_url');
        });
    }
};
