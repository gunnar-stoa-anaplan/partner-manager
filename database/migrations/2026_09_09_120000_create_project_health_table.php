<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PSM-entered implementation-health signals for a delivery project, keyed by the
 * warehouse project source_id. Scope / Timeline / Budget are RAG (G / Y / R);
 * Overall is derived from the worst of the three. Phase and reason are free text.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_health', function (Blueprint $table) {
            $table->id();
            $table->string('project_key')->unique();
            $table->string('phase')->nullable();
            $table->char('scope', 1)->nullable();
            $table->char('timeline', 1)->nullable();
            $table->char('budget', 1)->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_health');
    }
};
