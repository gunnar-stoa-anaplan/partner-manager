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
        Schema::create('launch_program_date_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('launch_program_id')->constrained()->cascadeOnDelete();
            $table->string('field');
            $table->date('old_date')->nullable();
            $table->date('new_date')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->constrained('users')->noActionOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('launch_program_date_changes');
    }
};
