<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('product_area')->nullable();
            $table->string('level')->default('associate');
            $table->string('status')->default('active');
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('exam_score')->nullable();
            $table->string('version')->nullable();
            $table->string('credential_id')->nullable();
            $table->string('source')->nullable();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
