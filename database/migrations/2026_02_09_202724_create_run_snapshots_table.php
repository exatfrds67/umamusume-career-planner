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
        Schema::create('ucp_run_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('ucp_careers')->cascadeOnDelete();
            $table->unsignedInteger('turn_number');
            $table->string('trigger_type', 50)->default('manual');
            $table->string('description')->nullable();
            $table->json('snapshot_data');
            $table->string('checksum', 64)->nullable();
            $table->timestamps();

            $table->index(['career_id', 'turn_number']);
            $table->index('trigger_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_run_snapshots');
    }
};
