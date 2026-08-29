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
        Schema::create('workshop_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained('workshops')->cascadeOnDelete();
            $table->string('slug');
            $table->string('title');
            $table->longText('body');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('checks_json')->nullable();
            $table->timestamps();

            $table->unique(['workshop_id', 'slug'], 'workshop_pages_workshop_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_pages');
    }
};
