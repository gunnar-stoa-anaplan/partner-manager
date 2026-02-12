<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('product_area')->nullable();
            $table->string('industry_focus')->nullable();
            $table->string('lifecycle_stage')->default('build');
            $table->dateTime('stage_entered_at');
            $table->string('status')->default('active');
            $table->string('demo_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->decimal('estimated_acv', 14, 2)->nullable();
            $table->integer('deployment_duration_weeks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_solutions');
    }
};
