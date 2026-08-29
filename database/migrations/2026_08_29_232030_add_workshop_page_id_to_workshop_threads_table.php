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
        Schema::table('workshop_threads', function (Blueprint $table) {
            $table->foreignId('workshop_page_id')->nullable()->after('workshop_session_id')->constrained('workshop_pages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_threads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workshop_page_id');
        });
    }
};
