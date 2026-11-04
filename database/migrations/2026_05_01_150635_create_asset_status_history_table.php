<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_asset_id')->constrained('journey_assets')->cascadeOnDelete();
            $table->enum('old_status', ['missing', 'in_review', 'needs_revision', 'approved'])->nullable();
            $table->enum('new_status', ['missing', 'in_review', 'needs_revision', 'approved']);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['journey_asset_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_status_history');
    }
};
