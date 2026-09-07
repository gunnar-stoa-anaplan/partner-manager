<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * First-class teams / functions (Sales, Marketing, Service, Implementation, …).
 * Membership is polymorphic so BOTH internal users and partner contacts can
 * belong to the same team — the axis that makes "partners as an extension of the
 * team" real, and a dimension for collaboration and visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->morphs('member'); // member_type + member_id (User or Contact)
            $table->string('role_in_team')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'member_type', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
