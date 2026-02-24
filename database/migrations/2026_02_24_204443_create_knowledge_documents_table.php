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
        if (Schema::connection('knowledge_base')->hasTable('knowledge_documents')) {
            return;
        }

        Schema::connection('knowledge_base')->create('knowledge_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('source');
            $table->string('status')->default('pending');
            $table->unsignedInteger('chunk_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->string('checksum')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('ingested_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('checksum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('knowledge_base')->dropIfExists('knowledge_documents');
    }
};
