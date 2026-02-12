<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telemetry_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->dateTime('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('telemetry_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->json('metrics');
            $table->dateTime('sent_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_heartbeats');
        Schema::dropIfExists('telemetry_events');
    }
};
