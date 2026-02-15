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
        Schema::create('lab_checkpoint_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_checkpoint_id')->constrained()->cascadeOnDelete();
            $table->boolean('passed')->default(false);
            $table->timestamp('validated_at');
            $table->json('validation_details')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->timestamps();

            $table->index(['lab_participant_id', 'lab_checkpoint_id'], 'lcr_participant_checkpoint_index');
            $table->index(['lab_checkpoint_id', 'passed'], 'lcr_checkpoint_passed_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_checkpoint_results');
    }
};
