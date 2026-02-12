<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('customer_satisfaction')->nullable();
            $table->boolean('is_referenceable')->default(false);
            $table->boolean('led_to_expansion')->default(false);
            $table->integer('scope_changes')->default(0);
            $table->date('go_live_date')->nullable();
            $table->text('outcome_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'customer_satisfaction',
                'is_referenceable',
                'led_to_expansion',
                'scope_changes',
                'go_live_date',
                'outcome_notes',
            ]);
        });
    }
};
