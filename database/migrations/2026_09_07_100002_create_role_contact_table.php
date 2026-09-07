<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-role for partner contacts. Additive: contacts.portal_role_id stays as
 * the "primary" role; this pivot holds the full set.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['contact_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_contact');
    }
};
