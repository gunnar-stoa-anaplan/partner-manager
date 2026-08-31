<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE workshop_threads MODIFY kind ENUM('question', 'comment', 'content_flag') NOT NULL DEFAULT 'question'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE workshop_threads SET kind = 'question' WHERE kind = 'content_flag'");
        DB::statement("ALTER TABLE workshop_threads MODIFY kind ENUM('question', 'comment') NOT NULL DEFAULT 'question'");
    }
};
