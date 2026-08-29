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
        Schema::create('workshop_credential_pool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained('workshops')->cascadeOnDelete();
            $table->string('email');
            $table->string('username');
            $table->text('password_enc');
            $table->enum('status', ['available', 'maintenance', 'retired'])->default('available');
            $table->dateTime('last_refreshed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['workshop_id', 'username'], 'workshop_credential_pool_workshop_username_unique');
            $table->index(['workshop_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_credential_pool');
    }
};
