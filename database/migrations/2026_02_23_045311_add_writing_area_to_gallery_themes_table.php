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
        Schema::table('gallery_themes', function (Blueprint $table) {
            $table->json('writing_area')->nullable()->after('frame_slots');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_themes', function (Blueprint $table) {
            $table->dropColumn('writing_area');
        });
    }
};
