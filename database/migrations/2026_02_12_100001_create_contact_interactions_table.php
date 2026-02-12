<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('logged_by')->constrained('users')->cascadeOnDelete();
            $table->string('interaction_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('interaction_date');
            $table->string('location')->nullable();
            $table->text('outcome')->nullable();
            $table->text('next_steps')->nullable();
            $table->foreignId('partner_event_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['partner_id', 'interaction_date']);
            $table->index(['contact_id', 'interaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_interactions');
    }
};
