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
        Schema::connection('knowledge_base')->ensureVectorExtensionExists();

        if (Schema::connection('knowledge_base')->hasTable('knowledge_chunks')) {
            return;
        }

        Schema::connection('knowledge_base')->create('knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('knowledge_documents')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->text('content');
            $table->vector('embedding', dimensions: 1536)->index();
            $table->unsignedInteger('word_count');
            $table->unsignedInteger('start_page')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('document_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally a no-op: KB data lives on a separate PostgreSQL database
     * and must never be dropped by migrate:refresh or migrate:rollback.
     */
    public function down(): void
    {
        //
    }
};
