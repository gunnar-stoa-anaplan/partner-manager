<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anaplan_workspaces', function (Blueprint $table) {
            $table->string('workspace_type')->nullable()->after('active');
            $table->string('product_area')->nullable()->after('workspace_type');
        });

        Schema::table('anaplan_models', function (Blueprint $table) {
            $table->string('detected_version')->nullable()->after('category');
            $table->string('product_area')->nullable()->after('detected_version');
        });
    }

    public function down(): void
    {
        Schema::table('anaplan_models', function (Blueprint $table) {
            $table->dropColumn(['detected_version', 'product_area']);
        });

        Schema::table('anaplan_workspaces', function (Blueprint $table) {
            $table->dropColumn(['workspace_type', 'product_area']);
        });
    }
};
