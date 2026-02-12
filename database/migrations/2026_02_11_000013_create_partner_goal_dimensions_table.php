<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_goal_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_goal_id')->constrained()->cascadeOnDelete();
            $table->string('dimension');
            $table->decimal('target_value', 14, 2);
            $table->decimal('actual_value', 14, 2)->nullable();
            $table->string('unit')->default('count');
            $table->string('label')->nullable();
            $table->decimal('q1_target', 14, 2)->nullable();
            $table->decimal('q2_target', 14, 2)->nullable();
            $table->decimal('q3_target', 14, 2)->nullable();
            $table->decimal('q4_target', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_goal_dimensions');
    }
};
