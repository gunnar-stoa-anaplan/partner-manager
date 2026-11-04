<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journeys', function (Blueprint $table) {
            $table->decimal('linked_pipeline_value', 14, 2)->nullable()->after('description');
            $table->decimal('linked_win_rate', 5, 2)->nullable()->after('linked_pipeline_value');
            $table->decimal('delivery_zero_trouble_rate', 5, 2)->nullable()->after('linked_win_rate');
            $table->timestamp('pillar_metrics_updated_at')->nullable()->after('delivery_zero_trouble_rate');
        });
    }

    public function down(): void
    {
        Schema::table('journeys', function (Blueprint $table) {
            $table->dropColumn([
                'linked_pipeline_value',
                'linked_win_rate',
                'delivery_zero_trouble_rate',
                'pillar_metrics_updated_at',
            ]);
        });
    }
};
