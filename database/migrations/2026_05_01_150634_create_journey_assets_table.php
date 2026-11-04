<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();
            $table->unsignedTinyInteger('stage_number');
            $table->string('task_id', 191);
            $table->string('asset_name');
            $table->string('asset_kind', 64)->nullable();
            $table->text('description')->nullable();
            $table->boolean('required')->default(true);
            $table->foreignId('linked_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->enum('status', ['missing', 'in_review', 'needs_revision', 'approved'])->default('missing');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();

            $table->index(['journey_id', 'stage_number']);
            $table->index(['journey_id', 'status']);
            $table->index('assigned_to_user_id');
            $table->unique(['journey_id', 'stage_number', 'task_id', 'asset_name'], 'journey_assets_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_assets');
    }
};
