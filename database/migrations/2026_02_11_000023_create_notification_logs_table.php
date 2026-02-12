<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_template_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->string('recipient_type');
            $table->unsignedBigInteger('recipient_id');
            $table->string('channel');
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('queued');
            $table->dateTime('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
