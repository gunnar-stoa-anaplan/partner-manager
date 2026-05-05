<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_stage_assets', function (Blueprint $table) {
            $table->id();
            $table->string('journey_key', 191);
            $table->unsignedTinyInteger('stage');
            $table->foreignId('asset_id')->constrained('content_assets')->cascadeOnDelete();
            $table->boolean('required')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['journey_key', 'stage', 'display_order'], 'jsa_lookup_idx');
            $table->unique(['journey_key', 'stage', 'asset_id'], 'jsa_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_stage_assets');
    }
};
