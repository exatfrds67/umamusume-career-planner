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
        Schema::create('ucp_rival_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_id')->index();
            $table->string('race_id');
            $table->string('distance')->nullable();
            $table->string('rival_uma')->nullable();
            $table->string('outcome')->default('won');
            $table->json('skill_hints')->nullable();
            $table->timestamps();

            // Add a foreign key to careers table
            $table->foreign('career_id')->references('id')->on('ucp_careers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_rival_logs');
    }
};
