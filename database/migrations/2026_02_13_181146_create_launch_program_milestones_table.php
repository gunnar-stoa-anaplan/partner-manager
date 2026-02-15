<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_program_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('launch_program_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->date('due_date')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('type')->default('task');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_program_milestones');
    }
};
