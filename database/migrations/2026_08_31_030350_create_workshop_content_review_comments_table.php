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
        Schema::create('workshop_content_review_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_page_id')->nullable()
                ->constrained('workshop_pages', indexName: 'wcrc_page_fk')->cascadeOnDelete();
            $table->foreignId('workshop_survey_template_id')->nullable()
                ->constrained('workshop_survey_templates', indexName: 'wcrc_survey_template_fk')->cascadeOnDelete();
            $table->foreignId('author_user_id')->constrained('users', indexName: 'wcrc_author_fk')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_content_review_comments');
    }
};
