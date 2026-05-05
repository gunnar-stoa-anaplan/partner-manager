<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_stage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();
            $table->unsignedTinyInteger('stage_number');
            $table->string('item_type', 32);
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('required')->default(false);
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['journey_id', 'stage_number', 'position']);
            $table->index(['item_type', 'item_id']);
            $table->unique(['journey_id', 'stage_number', 'item_type', 'item_id'], 'jsi_unique_per_stage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_stage_items');
    }
};
