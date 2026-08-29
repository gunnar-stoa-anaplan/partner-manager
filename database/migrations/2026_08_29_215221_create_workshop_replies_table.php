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
        Schema::create('workshop_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_thread_id')->constrained('workshop_threads')->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('author_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->boolean('is_monitor_reply')->default(false);
            $table->text('body');
            $table->dateTime('hidden_at')->nullable();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('workshop_thread_id', 'workshop_replies_thread_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_replies');
    }
};
