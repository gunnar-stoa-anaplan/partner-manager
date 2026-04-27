<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('type', 64);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('external_id')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['active', 'deprecated', 'retired'])->default('active');
            $table->string('scope')->default('global');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index('scope');
            $table->unique(['type', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
