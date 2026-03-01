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
        Schema::table('activation_path_user', function (Blueprint $table) {
            $table->timestamp('training_bypassed_at')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activation_path_user', function (Blueprint $table) {
            $table->dropColumn('training_bypassed_at');
        });
    }
};
