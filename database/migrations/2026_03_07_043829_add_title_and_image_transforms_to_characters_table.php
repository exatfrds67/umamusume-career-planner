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
            $table->string('title', 100)->nullable()->after('name');
            $table->float('image_x')->default(0)->after('avatar_url');
            $table->float('image_y')->default(0)->after('image_x');
            $table->float('image_zoom')->default(1.0)->after('image_y');
            $table->smallInteger('image_rotation')->unsigned()->default(0)->after('image_zoom');
            $table->boolean('image_flip_h')->default(false)->after('image_rotation');
            $table->string('avatar_processed', 500)->nullable()->after('image_flip_h');
            $table->string('avatar_circular', 500)->nullable()->after('avatar_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ucp_characters', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'image_x',
                'image_y',
                'image_zoom',
                'image_rotation',
                'image_flip_h',
                'avatar_processed',
                'avatar_circular',
            ]);
        });
    }
};
