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
        Schema::create('workshop_session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_session_id')->constrained('workshop_sessions')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->enum('status', ['invited', 'active', 'removed'])->default('invited');
            $table->dateTime('enrolled_at')->nullable();
            $table->foreignId('enrolled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('enrolled_by_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->dateTime('removed_at')->nullable();
            $table->timestamps();

            $table->unique(['workshop_session_id', 'contact_id'], 'workshop_session_participants_session_contact_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_session_participants');
    }
};
