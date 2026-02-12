<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certification_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_path_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('partner_path_stages')->nullOnDelete();
            $table->string('certification_type');
            $table->integer('minimum_count');
            $table->string('product_area')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certification_requirements');
    }
};
