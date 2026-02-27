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
        Schema::create('gallery_themes', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name', 100);
            $table->string('slug', 64)->unique();
            $table->string('description', 500)->nullable();
            $table->string('background_image_path', 500);
            $table->string('template_overlay_path', 500)->nullable();
            $table->string('thumbnail_path', 500)->nullable();
            $table->json('frame_slots');
            $table->string('aspect_ratio', 20)->default('16 / 9');
            $table->unsignedTinyInteger('max_frames')->default(6);
            $table->boolean('greyscale')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_themes');
    }
};
