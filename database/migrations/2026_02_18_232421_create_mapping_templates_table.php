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
        Schema::create('mapping_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('target_model');
            $table->string('version', 10);
            $table->string('required_feature')->nullable();
            $table->boolean('is_current')->default(true);
            $table->json('fields');
            $table->text('changelog')->nullable();
            $table->timestamps();

            $table->unique(['slug', 'version']);
            $table->index('target_model');
            $table->index('is_current');
            $table->index('required_feature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapping_templates');
    }
};
