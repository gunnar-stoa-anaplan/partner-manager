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
        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->enum('audience', ['participant', 'facilitator'])->default('participant')->after('workshop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
};
