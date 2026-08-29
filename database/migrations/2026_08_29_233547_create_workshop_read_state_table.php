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
        Schema::create('workshop_read_state', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_thread_id')->constrained('workshop_threads')->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('author_contact_id')->nullable()->constrained('contacts')->cascadeOnDelete();
            $table->dateTime('last_read_at');

            $table->unique(['workshop_thread_id', 'author_user_id', 'author_contact_id'], 'workshop_read_state_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_read_state');
    }
};
