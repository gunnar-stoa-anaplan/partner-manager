<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enablement_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('navigation');
            $table->string('product_area')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('requires_auth')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->decimal('estimated_duration_hours', 5, 2)->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        Schema::create('workshop_guide_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_guide_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('section');
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('lab_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['workshop_guide_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_guide_pages');
        Schema::dropIfExists('workshop_guides');
    }
};
