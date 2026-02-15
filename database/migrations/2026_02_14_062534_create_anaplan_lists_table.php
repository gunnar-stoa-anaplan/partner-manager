<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anaplan_lists', function (Blueprint $table) {
            $table->id();
            $table->string('anaplan_id');
            $table->string('name');
            $table->foreignId('anaplan_model_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('item_count')->nullable();
            $table->boolean('has_selective_access')->default(false);
            $table->timestamps();

            $table->unique(['anaplan_id', 'anaplan_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anaplan_lists');
    }
};
