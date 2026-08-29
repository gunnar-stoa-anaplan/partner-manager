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
        Schema::create('workshop_presence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_session_id')->constrained('workshop_sessions')->cascadeOnDelete();
            $table->foreignId('workshop_page_id')->nullable()->constrained('workshop_pages')->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('author_contact_id')->nullable()->constrained('contacts')->cascadeOnDelete();
            $table->dateTime('last_seen_at');

            $table->unique(['workshop_session_id', 'workshop_page_id', 'author_user_id', 'author_contact_id'], 'workshop_presence_unique');
            $table->index(['workshop_session_id', 'last_seen_at'], 'workshop_presence_session_seen_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_presence');
    }
};
