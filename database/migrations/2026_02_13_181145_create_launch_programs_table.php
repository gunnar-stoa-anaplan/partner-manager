<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->date('starts_at')->nullable();
            $table->date('target_ga_date')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedInteger('max_partners')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_programs');
    }
};
