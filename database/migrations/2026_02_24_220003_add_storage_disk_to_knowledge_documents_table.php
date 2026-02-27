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
        if (Schema::connection('knowledge_base')->hasColumn('knowledge_documents', 'storage_disk')) {
            return;
        }

        Schema::connection('knowledge_base')->table('knowledge_documents', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('source');
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
