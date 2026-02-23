<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 40);
            $table->string('type', 80);
            $table->string('severity', 20);
            $table->string('title');
            $table->text('description');
            $table->text('recommended_action')->nullable();
            $table->float('confidence_score');
            $table->string('source_processor');
            $table->string('correlation_id', 64)->nullable();
            $table->json('data')->nullable();
            $table->json('related_entity_ids')->nullable();
            $table->string('status', 20)->default('active');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('linked_alert_id')->nullable()->constrained('partner_alerts')->nullOnDelete();
            $table->timestamps();

            $table->index(['partner_id', 'status', 'channel']);
            $table->index(['channel', 'status']);
            $table->index(['source_processor', 'created_at']);
            $table->index('correlation_id');
            $table->index('expires_at');
        });

        Schema::create('signal_engine_runs', function (Blueprint $table) {
            $table->id();
            $table->string('processor')->nullable();
            $table->integer('signals_created');
            $table->integer('signals_expired');
            $table->integer('signals_correlated');
            $table->integer('partners_processed');
            $table->integer('duration_ms');
            $table->string('status', 20);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_engine_runs');
        Schema::dropIfExists('signals');
    }
};
