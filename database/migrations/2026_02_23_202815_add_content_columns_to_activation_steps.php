<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add Tell/Show content columns to activation_steps.
     */
    public function up(): void
    {
        Schema::table('activation_steps', function (Blueprint $table) {
            $table->longText('content_body')->nullable()->after('description');
            $table->json('objectives')->nullable()->after('content_body');
            $table->string('tour_route', 255)->nullable()->after('objectives');
            $table->json('tour_steps')->nullable()->after('tour_route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activation_steps', function (Blueprint $table) {
            $table->dropColumn(['content_body', 'objectives', 'tour_route', 'tour_steps']);
        });
    }
};
