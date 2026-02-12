<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('source')->default('psm_initiative');
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->string('owner_type')->default('psm');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->date('due_date');
            $table->dateTime('completed_at')->nullable();
            $table->text('outcome')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->string('fiscal_quarter')->nullable();
            $table->foreignId('partner_event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('linked_task_id')->nullable()->constrained('partner_tasks')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_plans');
    }
};
