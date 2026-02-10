<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('product_area')->nullable()->after('revenue_type');
        });

        Schema::table('opportunity_snapshots', function (Blueprint $table) {
            $table->string('product_area')->nullable()->after('revenue_type');
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('product_area');
        });

        Schema::table('opportunity_snapshots', function (Blueprint $table) {
            $table->dropColumn('product_area');
        });
    }
};
