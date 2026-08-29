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
        Schema::table('workshop_survey_responses', function (Blueprint $table) {
            $table->boolean('is_anonymous')->default(false)->after('contact_id');
            $table->string('raw_email')->nullable()->after('is_anonymous');
            $table->enum('source', ['live', 'import'])->default('live')->after('raw_email');
            $table->string('import_batch')->nullable()->after('source');
        });

        // contact_id must become nullable for anonymous/unmatched CSV-imported
        // responses. No doctrine/dbal installed, so drop and re-add the FK
        // via raw SQL rather than Blueprint::change().
        Schema::table('workshop_survey_responses', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
        });

        DB::statement('ALTER TABLE workshop_survey_responses MODIFY contact_id BIGINT UNSIGNED NULL');

        Schema::table('workshop_survey_responses', function (Blueprint $table) {
            $table->foreign('contact_id')->references('id')->on('contacts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_survey_responses', function (Blueprint $table) {
            $table->dropColumn(['is_anonymous', 'raw_email', 'source', 'import_batch']);
        });
    }
};
