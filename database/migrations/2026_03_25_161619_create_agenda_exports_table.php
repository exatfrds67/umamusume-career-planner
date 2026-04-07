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
        Schema::create('ucp_agenda_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_id')->index();
            $table->string('file_path')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->foreign('career_id')->references('id')->on('ucp_careers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_agenda_exports');
    }
};
