<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_path_gates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_stage_id')->constrained('partner_path_stages')->cascadeOnDelete();
            $table->foreignId('to_stage_id')->constrained('partner_path_stages')->cascadeOnDelete();
            $table->string('requirement_key');
            $table->string('requirement_label');
            $table->string('comparison')->default('gte');
            $table->decimal('threshold_value', 14, 2);
            $table->string('unit')->default('count');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_path_gates');
    }
};
