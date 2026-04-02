<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_path_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_path_id')->constrained()->cascadeOnDelete();
            $table->integer('stage_number');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#6C757D');
            $table->string('icon')->nullable();
            $table->integer('sort_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_path_stages');
    }
};
