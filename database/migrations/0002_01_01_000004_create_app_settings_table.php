<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            ['key' => 'fiscal_year_start_month', 'value' => '2', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_currency', 'value' => 'USD', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'historical_lookback_weeks', 'value' => '12', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_name', 'value' => 'Anaplan', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
