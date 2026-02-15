<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_program_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('launch_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('nominated');
            $table->foreignId('nominated_by')->constrained('users');
            $table->timestamp('accepted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['launch_program_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_program_partners');
    }
};
