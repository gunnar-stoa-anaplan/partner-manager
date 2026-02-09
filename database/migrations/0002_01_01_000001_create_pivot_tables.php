<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Account <-> Partner (many-to-many)
        Schema::create('account_partner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Contact <-> Project (many-to-many)
        Schema::create('contact_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // User <-> Partner (many-to-many with role)
        Schema::create('user_partner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('psm')->index();
            $table->timestamps();

            $table->unique(['user_id', 'partner_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_partner');
        Schema::dropIfExists('contact_project');
        Schema::dropIfExists('account_partner');
    }
};
