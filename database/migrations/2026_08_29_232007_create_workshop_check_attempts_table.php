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
        Schema::create('workshop_check_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->foreignId('workshop_session_id')->constrained('workshop_sessions')->cascadeOnDelete();
            $table->foreignId('workshop_page_id')->constrained('workshop_pages')->cascadeOnDelete();
            $table->string('check_id');
            $table->boolean('passed')->default(true);
            $table->dateTime('attempted_at');
            $table->timestamps();

            $table->index(['workshop_session_id', 'contact_id'], 'workshop_check_attempts_session_contact_idx');
            $table->index(['workshop_page_id', 'check_id'], 'workshop_check_attempts_page_check_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_check_attempts');
    }
};
