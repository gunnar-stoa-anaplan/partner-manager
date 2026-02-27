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
        Schema::create('integration_connections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');                          // csv_upload, rest_api, salesforce, webhook, database
            $table->string('direction')->default('both');    // import, export, both
            $table->json('config')->nullable();              // type-specific settings (base_url, paths, etc.)
            $table->text('auth_config')->nullable();         // encrypted credentials
            $table->string('status')->default('active');     // active, inactive, error
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_connections');
    }
};
