<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anaplan_models', function (Blueprint $table) {
            $table->id();
            $table->string('anaplan_id')->unique();
            $table->string('name');
            $table->foreignId('anaplan_workspace_id')->constrained()->cascadeOnDelete();
            $table->string('active_state')->nullable();
            $table->string('model_url', 512)->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('memory_usage')->nullable();
            $table->timestamp('created_in_anaplan_at')->nullable();
            $table->timestamp('last_modified_in_anaplan_at')->nullable();
            $table->string('last_modified_by_guid')->nullable();
            $table->unsignedBigInteger('last_saved_serial_number')->nullable();
            $table->unsignedInteger('module_count')->nullable();
            $table->unsignedInteger('list_count')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('anaplan_workspace_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anaplan_models');
    }
};
