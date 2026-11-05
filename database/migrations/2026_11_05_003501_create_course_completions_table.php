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
        Schema::create('course_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('resource_code')->index();
            $table->string('person_name');
            $table->string('course_name');
            $table->date('completed_on')->nullable()->index();
            $table->string('partner_hq')->nullable();
            $table->string('partner_region')->nullable();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['resource_code', 'course_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_completions');
    }
};
