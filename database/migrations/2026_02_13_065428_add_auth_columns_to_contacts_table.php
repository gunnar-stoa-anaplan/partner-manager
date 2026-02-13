<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken()->after('password');
            $table->timestamp('email_verified_at')->nullable()->after('remember_token');
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->boolean('portal_access')->default(false)->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token', 'email_verified_at', 'last_login_at', 'portal_access']);
        });
    }
};
