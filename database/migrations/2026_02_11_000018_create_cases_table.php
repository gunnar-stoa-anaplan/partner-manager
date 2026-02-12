<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('account_name')->nullable();
            $table->string('case_number')->nullable()->unique();
            $table->string('subject');
            $table->string('severity')->default('medium');
            $table->string('status')->default('open');
            $table->string('category')->nullable();
            $table->date('opened_date');
            $table->date('resolved_date')->nullable();
            $table->boolean('escalated')->default(false);
            $table->text('escalation_reason')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->integer('resolution_days')->nullable();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
