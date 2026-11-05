<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Salesforce becomes the source of truth for products: one row per Salesforce Product2 record.
     * Product names repeat across SKUs (e.g. "Model Builder (s)" under several product codes), so the
     * Salesforce ID becomes the unique key instead of the name.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('salesforce_id')->nullable()->unique()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->dropUnique(['name']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->unique('name');
            $table->dropColumn(['salesforce_id', 'description']);
        });
    }
};
