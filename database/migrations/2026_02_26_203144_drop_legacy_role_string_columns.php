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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('portal_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('psm')->after('password');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('portal_role')->nullable()->after('portal_access');
        });
    }
};
