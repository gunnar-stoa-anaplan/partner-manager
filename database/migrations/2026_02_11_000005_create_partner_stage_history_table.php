<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained('partner_path_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->constrained('partner_path_stages');
            $table->dateTime('transitioned_at');
            $table->foreignId('approved_by')->constrained('users');
            $table->json('gate_results')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_stage_history');
    }
};
