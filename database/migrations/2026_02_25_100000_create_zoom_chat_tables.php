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
        Schema::create('zoom_chat_responders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enablement_session_id')->nullable()->constrained('enablement_sessions')->nullOnDelete();
            $table->string('zoom_meeting_id');
            $table->string('agent_class');
            $table->string('display_name')->default('AI Assistant');
            $table->string('trigger_prefix', 50)->default('@bot');
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('max_response_tokens')->default(512);
            $table->unsignedInteger('rate_limit_per_minute')->default(10);
            $table->json('config')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('zoom_meeting_id');
            $table->index('is_active');
        });

        Schema::create('zoom_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zoom_chat_responder_id')->constrained()->cascadeOnDelete();
            $table->string('zoom_message_id')->unique();
            $table->string('sender_name');
            $table->string('sender_email')->nullable();
            $table->text('message_text');
            $table->text('response_text')->nullable();
            $table->string('agent_class');
            $table->string('status')->default('received');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->timestamps();

            $table->index(['zoom_chat_responder_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_chat_messages');
        Schema::dropIfExists('zoom_chat_responders');
    }
};
