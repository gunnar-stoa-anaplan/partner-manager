<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_scorecards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->date('scored_at')->index();
            $table->decimal('overall_score', 5, 1);
            $table->decimal('certification_score', 5, 1);
            $table->decimal('pipeline_score', 5, 1);
            $table->decimal('delivery_score', 5, 1);
            $table->decimal('engagement_score', 5, 1);
            $table->decimal('growth_score', 5, 1);
            $table->decimal('self_sufficiency_score', 5, 1);
            $table->string('tier')->nullable();
            $table->boolean('gate_eligible')->default(false);
            $table->json('score_details')->nullable();
            $table->json('scoring_config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_scorecards');
    }
};
