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
        Schema::create('ucp_agenda_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_id')->unique();
            $table->integer('reserved_coins')->default(0);
            $table->integer('reserved_hammers')->default(0);
            $table->string('target_ts_distance')->nullable();
            $table->timestamps();

            $table->foreign('career_id')->references('id')->on('ucp_careers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_agenda_reservations');
    }
};
