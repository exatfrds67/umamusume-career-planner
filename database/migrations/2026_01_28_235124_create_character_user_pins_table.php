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
        Schema::create('ucp_character_user_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('ucp_users')->onDelete('cascade');
            $table->foreignId('character_id')->constrained('ucp_characters')->onDelete('cascade');
            $table->timestamps();

            // Ensure a user can only pin a character once
            $table->unique(['user_id', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucp_character_user_pins');
    }
};
