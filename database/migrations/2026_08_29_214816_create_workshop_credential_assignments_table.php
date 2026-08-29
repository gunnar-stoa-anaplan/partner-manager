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
        Schema::create('workshop_credential_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_credential_id')->constrained('workshop_credential_pool')->cascadeOnDelete();
            $table->foreignId('workshop_session_id')->constrained('workshop_sessions')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->dateTime('assigned_at')->nullable();
            $table->date('hold_until')->nullable();
            $table->dateTime('released_at')->nullable();
            $table->string('released_reason')->nullable();
            $table->timestamps();

            $table->index('workshop_credential_id', 'workshop_credential_assignments_credential_idx');
            $table->index('workshop_session_id', 'workshop_credential_assignments_session_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_credential_assignments');
    }
};
