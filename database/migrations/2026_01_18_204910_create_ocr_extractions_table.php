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
        Schema::create('ucp_ocr_extractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_hash')->index();
            $table->text('extracted_text')->nullable();
            $table->json('parsed_data')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('data_type');
            $table->string('status')->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('processing_metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_ocr_extractions');
    }
};
