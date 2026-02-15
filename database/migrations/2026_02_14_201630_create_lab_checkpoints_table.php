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
        Schema::create('lab_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('checkpoint_type', ['formula', 'structure', 'config', 'value', 'list']);
            $table->json('validation_config');
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('points')->default(1);
            $table->text('hint')->nullable();
            $table->timestamps();

            $table->index(['lab_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_checkpoints');
    }
};
