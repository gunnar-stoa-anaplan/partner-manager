<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('psm_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 50)->default('general');
            $table->string('metric_type', 50)->nullable();
            $table->json('metric_config')->nullable();
            $table->decimal('target_value', 12, 2);
            $table->decimal('baseline_value', 12, 2)->default(0);
            $table->decimal('current_value', 12, 2)->default(0);
            $table->date('target_date');
            $table->string('status', 20)->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('suggested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_signal', 100)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['partner_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psm_objectives');
    }
};
