<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_plan_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('status')->default('pending');
            $table->date('due_date')->nullable();
            $table->string('assigned_to')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_plan_items');
    }
};
