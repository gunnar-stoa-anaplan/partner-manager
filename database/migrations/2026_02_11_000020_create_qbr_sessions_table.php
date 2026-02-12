<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qbr_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('fiscal_quarter');
            $table->string('status')->default('planning');
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->foreignId('partner_event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prepared_by')->constrained('users');
            $table->json('attendees')->nullable();
            $table->string('overall_rating')->nullable();
            $table->text('executive_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qbr_sessions');
    }
};
