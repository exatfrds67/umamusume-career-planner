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
        Schema::table('ucp_careers', function (Blueprint $table) {
            $table->unsignedTinyInteger('star_level')->default(3)->after('character_id')
                ->comment('Character star level (1-5). Affects base stats and unique skill availability.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_careers', function (Blueprint $table) {
            $table->dropColumn('star_level');
        });
    }
};
