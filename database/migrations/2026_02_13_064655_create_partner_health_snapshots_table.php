<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_health_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 1)->default(0);
            $table->string('risk_level', 20)->default('healthy');
            $table->json('signals')->nullable();
            $table->json('dimensions')->nullable();
            $table->date('snapshot_date');
            $table->timestamps();

            $table->unique(['partner_id', 'snapshot_date']);
            $table->index(['risk_level', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_health_snapshots');
    }
};
