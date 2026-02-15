<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anaplan_workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('anaplan_id')->unique();
            $table->string('name');
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('size_allowance')->nullable();
            $table->unsignedBigInteger('current_size')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anaplan_workspaces');
    }
};
