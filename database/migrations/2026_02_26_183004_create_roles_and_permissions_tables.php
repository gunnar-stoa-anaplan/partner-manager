<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('display_name', 100);
            $table->string('type', 50)->index(); // 'internal' or 'portal'
            $table->boolean('is_protected')->default(false);
            $table->boolean('bypass_permissions')->default(false);
            $table->string('landing_route', 100)->nullable();
            $table->string('dashboard_group', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('module_slug', 50);
            $table->string('access_level', 50); // 'read' or 'write'
            $table->timestamps();

            $table->unique(['role_id', 'module_slug']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('portal_role_id')->nullable()->after('portal_role')->constrained('roles');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('portal_role_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
    }
};
