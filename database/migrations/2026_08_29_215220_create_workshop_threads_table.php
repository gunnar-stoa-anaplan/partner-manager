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
        Schema::create('workshop_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_session_id')->constrained('workshop_sessions')->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('author_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->boolean('anonymous')->default(false);
            $table->enum('kind', ['question', 'comment'])->default('question');
            $table->text('body');
            $table->enum('status', ['open', 'answered', 'hidden'])->default('open');
            $table->dateTime('answered_at')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('hidden_at')->nullable();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['workshop_session_id', 'status'], 'workshop_threads_session_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_threads');
    }
};
