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
        Schema::table('workshop_survey_templates', function (Blueprint $table) {
            $table->unsignedTinyInteger('questions_per_attempt')->nullable()->after('max_attempts');
            $table->boolean('shuffle_options')->default(true)->after('questions_per_attempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_survey_templates', function (Blueprint $table) {
            $table->dropColumn(['questions_per_attempt', 'shuffle_options']);
        });
    }
};
