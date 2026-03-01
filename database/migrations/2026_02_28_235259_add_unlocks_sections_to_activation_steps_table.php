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
        Schema::table('activation_steps', function (Blueprint $table) {
            $table->json('unlocks_sections')->nullable()->after('phase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activation_steps', function (Blueprint $table) {
            $table->dropColumn('unlocks_sections');
        });
    }
};
