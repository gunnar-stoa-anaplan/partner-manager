<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_path_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('partner_path_stages')->nullOnDelete();
            $table->string('category');
            $table->string('requirement_level')->default('required');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('completion_criteria')->nullable();
            $table->string('effort_size')->default('medium');
            $table->string('default_owner')->default('psm');
            $table->integer('due_days_offset')->nullable();
            $table->string('auto_verify_rule')->nullable();
            $table->string('evidence_type')->default('psm_attestation');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('gate_requirement_id')->nullable()->constrained('partner_path_gates')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};
