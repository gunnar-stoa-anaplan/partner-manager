<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        Schema::table('workshop_sessions', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        DB::statement("ALTER TABLE workshop_sessions MODIFY status ENUM('upcoming', 'in_progress', 'completed', 'cancelled', 'archived') NOT NULL DEFAULT 'upcoming'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE workshop_sessions MODIFY status ENUM('upcoming', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming'");

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });

        Schema::table('workshop_sessions', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
