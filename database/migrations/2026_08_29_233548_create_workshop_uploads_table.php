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
        Schema::create('workshop_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_thread_id')->nullable()->constrained('workshop_threads')->cascadeOnDelete();
            $table->foreignId('workshop_reply_id')->nullable()->constrained('workshop_replies')->cascadeOnDelete();
            $table->string('filename');
            $table->string('stored_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('uploaded_by_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->timestamps();

            $table->index('workshop_thread_id', 'workshop_uploads_thread_idx');
            $table->index('workshop_reply_id', 'workshop_uploads_reply_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_uploads');
    }
};
