<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_path_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_stage_id')->constrained('resource_path_stages');
            $table->dateTime('enrolled_at');
            $table->dateTime('stage_entered_at');
            $table->dateTime('last_reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['contact_id', 'partner_id', 'resource_path_id'], 'resource_enrollment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_path_enrollments');
    }
};
