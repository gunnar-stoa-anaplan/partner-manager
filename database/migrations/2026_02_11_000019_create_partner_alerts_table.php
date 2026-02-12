<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('severity')->default('info');
            $table->string('title');
            $table->text('description');
            $table->text('recommended_action')->nullable();
            $table->json('data')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('resolved_at')->nullable();
            $table->string('auto_resolve_when')->nullable();
            $table->foreignId('linked_task_id')->nullable()->constrained('partner_tasks')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_alerts');
    }
};
