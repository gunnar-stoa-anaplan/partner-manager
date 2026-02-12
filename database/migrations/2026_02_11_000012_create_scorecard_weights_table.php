<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorecard_weights', function (Blueprint $table) {
            $table->id();
            $table->string('dimension')->unique();
            $table->decimal('weight', 3, 2);
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed default weights
        $now = now();
        DB::table('scorecard_weights')->insert([
            ['dimension' => 'certification', 'weight' => 0.15, 'is_active' => true, 'updated_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['dimension' => 'pipeline', 'weight' => 0.20, 'is_active' => true, 'updated_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['dimension' => 'delivery', 'weight' => 0.20, 'is_active' => true, 'updated_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['dimension' => 'engagement', 'weight' => 0.15, 'is_active' => true, 'updated_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['dimension' => 'growth', 'weight' => 0.20, 'is_active' => true, 'updated_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['dimension' => 'self_sufficiency', 'weight' => 0.10, 'is_active' => true, 'updated_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('scorecard_weights');
    }
};
