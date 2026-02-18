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
        Schema::create('ucp_critical_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_id');
            $table->integer('turn_number');
            $table->string('alert_type', 50);
            $table->text('message');
            $table->json('action_items');
            $table->integer('turns_until_critical')->nullable();
            $table->boolean('was_dismissed')->default(false);
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps(); // This adds both created_at and updated_at

            // Foreign key constraint
            $table->foreign('career_id')
                ->references('id')
                ->on('ucp_careers')
                ->onDelete('cascade');

            // Indexes
            $table->index(['career_id', 'turn_number']);
            $table->index(['career_id', 'was_dismissed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_critical_alerts');
    }
};
