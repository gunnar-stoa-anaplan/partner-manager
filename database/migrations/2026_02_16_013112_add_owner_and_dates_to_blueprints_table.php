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
        Schema::table('blueprints', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('created_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->after('status');
            $table->timestamp('archived_at')->nullable()->after('published_at');
            $table->timestamp('drafted_at')->nullable()->after('archived_at');
        });

        // Alter status enum to include 'submitted'
        DB::statement("ALTER TABLE blueprints MODIFY COLUMN status ENUM('draft','submitted','published','archived') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum
        DB::statement("UPDATE blueprints SET status = 'draft' WHERE status = 'submitted'");
        DB::statement("ALTER TABLE blueprints MODIFY COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'");

        Schema::table('blueprints', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn(['owner_id', 'published_at', 'archived_at', 'drafted_at']);
        });
    }
};
