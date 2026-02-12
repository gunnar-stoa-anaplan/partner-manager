<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('requirement_level')->default('required');
            $table->string('status')->default('not_started');
            $table->string('priority')->default('medium');
            $table->string('effort_size')->default('medium');
            $table->string('owner')->default('psm');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('completion_evidence')->nullable();
            $table->string('evidence_file')->nullable();
            $table->boolean('auto_verified')->default(false);
            $table->dateTime('auto_verified_at')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('waived_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('stage_id')->nullable()->constrained('partner_path_stages')->nullOnDelete();
            $table->foreignId('partner_path_enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_tasks');
    }
};
