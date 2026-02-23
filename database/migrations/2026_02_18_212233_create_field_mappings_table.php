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
        Schema::create('field_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->constrained('integration_connections')->cascadeOnDelete();
            $table->string('name');
            $table->string('source_field');                 // field name in external data
            $table->string('target_model');                 // Partner, Contact, Opportunity, etc.
            $table->string('target_field');                 // attribute on the model
            $table->string('transform')->nullable();        // lowercase, uppercase, trim, date_iso, integer, float, boolean
            $table->string('default_value')->nullable();    // used when source value is empty
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('connection_id');
            $table->index('target_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_mappings');
    }
};
