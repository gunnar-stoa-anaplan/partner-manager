<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qbr_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qbr_session_id')->constrained()->cascadeOnDelete();
            $table->string('section_type');
            $table->integer('sort_order')->default(0);
            $table->json('auto_generated_data')->nullable();
            $table->text('psm_notes')->nullable();
            $table->text('partner_feedback')->nullable();
            $table->string('rating')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qbr_sections');
    }
};
