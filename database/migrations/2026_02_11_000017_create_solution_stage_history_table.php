<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solution_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_solution_id')->constrained()->cascadeOnDelete();
            $table->string('from_stage')->nullable();
            $table->string('to_stage');
            $table->dateTime('transitioned_at');
            $table->foreignId('transitioned_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solution_stage_history');
    }
};
