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
            $table->string('whiteboard_image_path', 500)->nullable()->after('thumbnail_path');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_themes', function (Blueprint $table) {
            $table->dropColumn('whiteboard_image_path');
        });
    }
};
