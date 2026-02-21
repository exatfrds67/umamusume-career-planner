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
            $table->integer('finish_position')->nullable()->comment('Final finishing position')->change();
            $table->string('race_result')->default('pending')->comment('Race result status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_races', function (Blueprint $table) {
            $table->integer('finish_position')->comment('Final finishing position')->change();
            $table->string('race_result')->comment('Race result status')->change();
        });
    }
};
