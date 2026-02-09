<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', [
                'qbr', 'training', 'workshop', 'demo',
                'joint_sales_call', 'check_in', 'escalation_review',
                'certification_exam', 'kickoff', 'other'
            ]);
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->text('outcome')->nullable();
            $table->text('next_steps')->nullable();

            $table->foreignId('opportunity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('recurrence_type', ['none', 'daily', 'weekly', 'biweekly', 'monthly'])->default('none');
            $table->date('recurrence_end')->nullable();
            $table->foreignId('parent_event_id')->nullable()->constrained('partner_events')->nullOnDelete();

            $table->timestamps();

            $table->index(['partner_id', 'start_at']);
            $table->index(['start_at', 'end_at']);
            $table->index('status');
            $table->index('parent_event_id');
        });

        Schema::create('contact_partner_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_event_id')->constrained('partner_events')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contact_id', 'partner_event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_partner_event');
        Schema::dropIfExists('partner_events');
    }
};
