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
        DB::statement("ALTER TABLE workshop_survey_templates MODIFY kind ENUM('pre', 'post', 'knowledge') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE workshop_survey_templates MODIFY kind ENUM('pre', 'post') NOT NULL");
    }
};
